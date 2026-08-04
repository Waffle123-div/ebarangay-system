<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\DocumentRequest;

class NewDocumentRequestNotification extends Notification
{
    use Queueable;

    protected $documentRequest;

    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type'            => 'new_request',
            'tracking_number' => $this->documentRequest->tracking_number,
            'document_type'   => $this->documentRequest->document_type,
            'resident_name'   => $this->documentRequest->resident_name,
            'message'         => 'New document request: ' . $this->documentRequest->document_type . ' from ' . $this->documentRequest->resident_name . '.',
            'link'            => '/admin/requests',
        ];
    }
}
