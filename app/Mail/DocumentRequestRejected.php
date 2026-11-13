<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentRequestRejected extends Mailable
{
    use Queueable, SerializesModels;

    public string $trackingNumber;
    public string $documentType;
    public string $residentName;
    public ?string $purpose;
    public ?string $reason;

    public function __construct(
        string $trackingNumber,
        string $documentType,
        string $residentName,
        ?string $purpose = null,
        ?string $reason = null
    ) {
        $this->trackingNumber = $trackingNumber;
        $this->documentType = $documentType;
        $this->residentName = $residentName;
        $this->purpose = $purpose;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject("Your Document Request Was Rejected (#{$this->trackingNumber}) - E-Barangay")
            ->view('emails.document_rejected');
    }
}

