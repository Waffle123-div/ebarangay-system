<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\DocumentRequest;

class DocumentReady extends Mailable
{
    use Queueable, SerializesModels;

    public $documentRequest;

    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    public function build()
    {
        return $this->subject('Your Document is Ready for Pick-up - E-Barangay')
                    ->view('emails.document_ready');
    }
}