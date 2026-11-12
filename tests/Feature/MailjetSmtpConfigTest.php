<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailjetSmtpConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_configuration_uses_mailjet_smtp_defaults(): void
    {
        $this->assertStringContainsString("'default' => env('MAIL_MAILER', 'smtp')", file_get_contents(config_path('mail.php')));
        $this->assertSame('in-v3.mailjet.com', config('mail.mailers.smtp.host'));
        $this->assertEquals(587, config('mail.mailers.smtp.port'));
        $this->assertSame('susontroy5@gmail.com', config('mail.from.address'));
    }

    public function test_registration_otp_does_not_fallback_to_resend_when_smtp_is_active(): void
    {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.username', '');
        Config::set('mail.mailers.smtp.password', '');
        Config::set('services.resend.key', 're_dummy_stale_key');

        Http::fake();
        Mail::shouldReceive('to')
            ->once()
            ->with('resident.otp@example.com')
            ->andThrow(new \RuntimeException('SMTP connection failed'));

        $response = $this->postJson(route('resident.register.send_otp'), [
            'email' => 'resident.otp@example.com',
            'first_name' => 'Juan',
            'target_type' => 'email',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Email delivery error: SMTP credentials (MAIL_USERNAME / MAIL_PASSWORD) are not configured.',
            ]);

        Http::assertNothingSent();
    }

    public function test_registration_otp_reports_smtp_exception_message_when_credentials_set(): void
    {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.username', 'test_api_key');
        Config::set('mail.mailers.smtp.password', 'test_secret_key');

        Mail::shouldReceive('to')
            ->once()
            ->with('resident.smtp@example.com')
            ->andThrow(new \RuntimeException('535 5.7.8 Authentication failed'));

        $response = $this->postJson(route('resident.register.send_otp'), [
            'email' => 'resident.smtp@example.com',
            'first_name' => 'Maria',
            'target_type' => 'email',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Failed to send verification email: 535 5.7.8 Authentication failed',
            ]);
    }
}
