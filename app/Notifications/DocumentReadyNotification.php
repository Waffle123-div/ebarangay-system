<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\DocumentRequest;

class DocumentReadyNotification extends Notification
{
    use Queueable;

    protected $documentRequest;

    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    // We only want this in the database (since we already handle emails separately)
    public function via($notifiable)
    {
        return ['database'];
    }

    // This is the data that gets saved to the database and shown in the bell dropdown!
    public function toArray($notifiable)
    {
        return [
            'tracking_number' => $this->documentRequest->tracking_number,
            'document_type' => $this->documentRequest->document_type,
            'message' => 'Your ' . $this->documentRequest->document_type . ' is ready for pick-up!',
        ];
    }
}