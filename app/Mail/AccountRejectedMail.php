<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $residentName;

    public function __construct($residentName)
    {
        $this->residentName = $residentName;
    }

    public function build()
    {
        return $this->subject('Action Required: E-Barangay Registration Update')
                    ->view('emails.account_rejected');
    }
}