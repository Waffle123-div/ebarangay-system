<?php

namespace App\Mail\Transports;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class MailjetHttpTransport extends AbstractTransport
{
    protected string $apiKey;

    protected string $secretKey;

    public function __construct(?string $apiKey = null, ?string $secretKey = null)
    {
        parent::__construct();

        $this->apiKey = trim((string) (
            $apiKey
            ?: config('services.mailjet.key')
            ?: config('mail.mailers.smtp.username')
        ));

        $this->secretKey = trim((string) (
            $secretKey
            ?: config('services.mailjet.secret')
            ?: config('mail.mailers.smtp.password')
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function doSend(SentMessage $message): void
    {
        if ($this->apiKey === '' || $this->secretKey === '') {
            throw new \RuntimeException('Mailjet API credentials (MAILJET_API_KEY / MAILJET_SECRET_KEY or MAIL_USERNAME / MAIL_PASSWORD) are not configured.');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $fromList = $email->getFrom();
        $fromAddress = !empty($fromList)
            ? $fromList[0]->getAddress()
            : (string) config('mail.from.address', 'susontroy5@gmail.com');
        $fromName = !empty($fromList) && $fromList[0]->getName() !== ''
            ? $fromList[0]->getName()
            : (string) config('mail.from.name', 'E-Barangay Cogon East');

        $fromPayload = ['Email' => $fromAddress];
        if ($fromName !== '') {
            $fromPayload['Name'] = $fromName;
        }

        $formatAddresses = static function (array $addresses): array {
            return array_values(array_map(static function (Address $addr): array {
                $item = ['Email' => $addr->getAddress()];
                if ($addr->getName() !== '') {
                    $item['Name'] = $addr->getName();
                }

                return $item;
            }, $addresses));
        };

        $to = $formatAddresses($email->getTo());
        $cc = $formatAddresses($email->getCc());
        $bcc = $formatAddresses($email->getBcc());
        $replyTo = $email->getReplyTo();

        $subject = (string) ($email->getSubject() ?? 'E-Barangay System Notification');
        $html = $email->getHtmlBody();
        $text = $email->getTextBody();

        if (is_resource($html)) {
            $html = stream_get_contents($html);
        }
        if (is_resource($text)) {
            $text = stream_get_contents($text);
        }

        $mailjetMessage = [
            'From' => $fromPayload,
            'To' => $to,
            'Subject' => $subject,
        ];

        if (!empty($html)) {
            $mailjetMessage['HTMLPart'] = $html;
        }
        if (!empty($text)) {
            $mailjetMessage['TextPart'] = $text;
        }
        if (empty($html) && empty($text)) {
            $mailjetMessage['TextPart'] = ' ';
        }

        if (!empty($cc)) {
            $mailjetMessage['Cc'] = $cc;
        }
        if (!empty($bcc)) {
            $mailjetMessage['Bcc'] = $bcc;
        }
        if (!empty($replyTo)) {
            $mailjetMessage['Headers'] = [
                'Reply-To' => $replyTo[0]->toString(),
            ];
        }

        if (!empty($email->getAttachments())) {
            $attachmentsPayload = [];
            foreach ($email->getAttachments() as $attachment) {
                $body = $attachment->getBody();
                if (is_resource($body)) {
                    $body = stream_get_contents($body);
                }

                $attachmentsPayload[] = [
                    'ContentType' => $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
                    'Filename' => $attachment->getFilename() ?? 'attachment.bin',
                    'Base64Content' => base64_encode((string) $body),
                ];
            }

            if (!empty($attachmentsPayload)) {
                $mailjetMessage['Attachments'] = $attachmentsPayload;
            }
        }

        $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
            ->timeout(15)
            ->post('https://api.mailjet.com/v3.1/send', [
                'Messages' => [$mailjetMessage],
            ]);

        if (!$response->successful()) {
            $errorMsg = 'Mailjet API returned error ' . $response->status() . ': ' . $response->body();
            Log::error($errorMsg, ['to' => array_column($to, 'Email'), 'subject' => $subject]);
            throw new \RuntimeException($errorMsg);
        }

        $messageStatus = $response->json('Messages.0.Status');
        if ($messageStatus !== null && $messageStatus !== 'success') {
            $errorDetails = json_encode($response->json('Messages.0.Errors') ?? $response->json());
            $errorMsg = 'Mailjet delivery failed (' . $messageStatus . '): ' . $errorDetails;
            Log::error($errorMsg, ['to' => array_column($to, 'Email'), 'subject' => $subject]);
            throw new \RuntimeException($errorMsg);
        }

        Log::info('Email sent successfully via Mailjet API', [
            'message_id' => $response->json('Messages.0.To.0.MessageID'),
            'to' => array_column($to, 'Email'),
            'subject' => $subject,
        ]);
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'mailjet_http';
    }
}
