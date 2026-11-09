<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentRejectedNotification extends Notification
{
    use Queueable;

    protected string $trackingNumber;
    protected string $documentType;
    protected ?string $reason;

    public function __construct(string $trackingNumber, string $documentType, ?string $reason = null)
    {
        $this->trackingNumber = $trackingNumber;
        $this->documentType = $documentType;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $message = "Your request for {$this->documentType} (#{$this->trackingNumber}) has been rejected.";
        if (!empty($this->reason)) {
            $message .= " Reason: {$this->reason}";
        }

        return [
            'tracking_number' => $this->trackingNumber,
            'document_type'   => $this->documentType,
            'status'          => 'Rejected',
            'reason'          => $this->reason,
            'message'         => $message,
        ];
    }
}

