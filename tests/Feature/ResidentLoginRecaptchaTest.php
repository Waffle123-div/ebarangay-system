<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ResidentLoginRecaptchaTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_login_page_renders_recaptcha_when_site_key_configured(): void
    {
        Config::set('services.recaptcha.site_key', '6LemLRwuAAAAAPYzrDdOn-na4zxddesHAzhUl_Fe');

        $response = $this->get(route('resident.login'));

        $response->assertStatus(200);
        $response->assertSee('class="g-recaptcha"', false);
        $response->assertSee('data-sitekey="6LemLRwuAAAAAPYzrDdOn-na4zxddesHAzhUl_Fe"', false);
        $response->assertSee('https://www.google.com/recaptcha/api.js', false);
    }

    public function test_resident_login_validation_requires_recaptcha_when_secret_key_active(): void
    {
        Config::set('services.recaptcha.secret_key', 'test_secret_key');

        $response = $this->post(route('resident.login.submit'), [
            'email' => 'resident@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['g-recaptcha-response']);
    }

    public function test_resident_login_validation_passes_recaptcha_when_google_verifies(): void
    {
        Config::set('services.recaptcha.secret_key', 'test_secret_key');

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        $response = $this->post(route('resident.login.submit'), [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpass',
            'g-recaptcha-response' => 'valid-mock-token',
        ]);

        // Should not have recaptcha error (only credential error)
        $response->assertSessionDoesntHaveErrors(['g-recaptcha-response']);
    }

    public function test_resident_login_validation_passes_when_enterprise_token_submitted(): void
    {
        Config::set('services.recaptcha.secret_key', 'test_secret_key');

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $response = $this->post(route('resident.login.submit'), [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpass',
            'g-recaptcha-response' => str_repeat('A', 50),
        ]);

        $response->assertSessionDoesntHaveErrors(['g-recaptcha-response']);
    }
}

