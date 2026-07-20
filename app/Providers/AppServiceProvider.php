<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
           return (new MailMessage)
               ->subject('Action Required: Verify Your E-Barangay Account')
               ->greeting('Welcome to Barangay Cogon East!')
               ->line('You are receiving this email because an account registration was requested for the E-Barangay Document Request and Tracking System using this email address.')
               ->line('To securely verify your identity and unlock your Resident Dashboard, please click the button below:')
               ->action('Verify Email Address', $url)
               ->line('If you did not request this registration, no further action is required and your email remains secure.')
               ->salutation('Respectfully, The E-Barangay Administration');
       });
    }
}
