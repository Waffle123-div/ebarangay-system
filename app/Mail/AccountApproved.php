<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class AccountApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $user; // Create a variable to hold the user data

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your E-Barangay Account is Approved!', // The email subject line
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account_approved', // We will create this view next
        );
    }
}
