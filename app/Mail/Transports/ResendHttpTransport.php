<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Address;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendHttpTransport extends AbstractTransport
{
    protected string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        parent::__construct();
        $this->apiKey = $apiKey ?: (string) (config('services.resend.key') ?? env('RESEND_API_KEY'));
    }

    /**
     * {@inheritdoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        // Format sender
        $fromAddress = config('services.resend.from_address') ?: env('RESEND_FROM_ADDRESS');
        $fromName = config('mail.from.name', 'E-Barangay Cogon East');

        if (empty($fromAddress)) {
            $fromList = $email->getFrom();
            if (!empty($fromList)) {
                $first = $fromList[0];
                $addr = $first->getAddress();
                // Resend free tier requires onboarding@resend.dev unless custom domain is verified
                if (str_contains($addr, 'resend.dev') || (!str_contains($addr, 'gmail.com') && !str_contains($addr, 'example.com') && !str_contains($addr, 'localhost'))) {
                    $fromAddress = $addr;
                    $fromName = $first->getName() ?: $fromName;
                }
            }
        }

        if (empty($fromAddress)) {
            $fromAddress = 'onboarding@resend.dev';
        }

        $formattedFrom = !empty($fromName) ? "{$fromName} <{$fromAddress}>" : $fromAddress;

        // Recipients
        $to = array_map(fn (Address $addr) => $addr->getAddress(), $email->getTo());
        $cc = array_map(fn (Address $addr) => $addr->getAddress(), $email->getCc());
        $bcc = array_map(fn (Address $addr) => $addr->getAddress(), $email->getBcc());
        $replyTo = array_map(fn (Address $addr) => $addr->getAddress(), $email->getReplyTo());

        if (empty($replyTo)) {
            $defaultReply = config('mail.from.address', 'cogoneast.system@gmail.com');
            if (filled($defaultReply) && !str_contains($defaultReply, 'example.com')) {
                $replyTo = [$defaultReply];
            }
        }

        // Subject & Bodies
        $subject = (string) ($email->getSubject() ?? 'E-Barangay System Notification');
        $html = $email->getHtmlBody();
        $text = $email->getTextBody();

        if (is_resource($html)) {
            $html = stream_get_contents($html);
        }
        if (is_resource($text)) {
            $text = stream_get_contents($text);
        }

        $payload = [
            'from'    => $formattedFrom,
            'to'      => $to,
            'subject' => $subject,
        ];

        if (!empty($html)) {
            $payload['html'] = $html;
        } elseif (!empty($text)) {
            $payload['text'] = $text;
        } else {
            $payload['text'] = ' ';
        }

        if (!empty($replyTo)) {
            $payload['reply_to'] = count($replyTo) === 1 ? $replyTo[0] : $replyTo;
        }
        if (!empty($cc)) {
            $payload['cc'] = $cc;
        }
        if (!empty($bcc)) {
            $payload['bcc'] = $bcc;
        }

        // Attachments
        if (!empty($email->getAttachments())) {
            $attachmentsPayload = [];
            foreach ($email->getAttachments() as $attachment) {
                $body = $attachment->getBody();
                if (is_resource($body)) {
                    $body = stream_get_contents($body);
                }
                $attachmentsPayload[] = [
                    'filename' => $attachment->getFilename() ?? 'document.pdf',
                    'content'  => base64_encode($body),
                ];
            }
            if (!empty($attachmentsPayload)) {
                $payload['attachments'] = $attachmentsPayload;
            }
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(15)
            ->post('https://api.resend.com/emails', $payload);

        if (!$response->successful()) {
            $errorMsg = 'Resend API returned error ' . $response->status() . ': ' . $response->body();
            Log::error($errorMsg, ['to' => $to, 'subject' => $subject]);
            throw new \RuntimeException($errorMsg);
        }

        Log::info('Email sent successfully via Resend API', [
            'id' => $response->json('id'),
            'to' => $to,
            'subject' => $subject,
        ]);
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'resend_http';
    }
}
