<?php

namespace App\Notifications;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentAcceptedNotification extends Notification
{
    use Queueable;

    protected DocumentRequest $documentRequest;

    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    /**
     * Store notification in database for the resident portal bell.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Payload rendered in the resident portal notifications dropdown and API.
     */
    public function toArray($notifiable): array
    {
        $docType = $this->documentRequest->document_type;
        $trackingNumber = $this->documentRequest->tracking_number;

        return [
            'request_id'      => $this->documentRequest->id,
            'tracking_number' => $trackingNumber,
            'document_type'   => $docType,
            'status'          => 'Processing',
            'message'         => "Your request for {$docType} (#{$trackingNumber}) has been accepted and is now being processed.",
            'link'            => route('resident.request.track', [], false),
        ];
    }
}

