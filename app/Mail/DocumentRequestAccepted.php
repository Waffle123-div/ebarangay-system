<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentRequestAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;

    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    public function build()
    {
        return $this->subject("Your Document Request Is Now Being Processed (#{$this->documentRequest->tracking_number}) - E-Barangay")
            ->view('emails.document_accepted');
    }
}

