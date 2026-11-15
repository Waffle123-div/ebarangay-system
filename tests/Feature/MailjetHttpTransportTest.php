<?php

namespace Tests\Feature;

use App\Mail\DocumentRequestRejected;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailjetHttpTransportTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailjet_http_transport_sends_document_rejected_email_via_mailjet_v31_api(): void
    {
        Config::set('mail.default', 'mailjet');
        Config::set('mail.from.address', 'susontroy5@gmail.com');
        Config::set('mail.from.name', 'E-Barangay Cogon East');
        Config::set('mail.mailers.mailjet.key', 'mj_test_api_key');
        Config::set('mail.mailers.mailjet.secret', 'mj_test_secret_key');
        Mail::purge('mailjet');

        Http::fake([
            'https://api.mailjet.com/v3.1/send' => Http::response([
                'Messages' => [
                    [
                        'Status' => 'success',
                        'To' => [
                            ['Email' => 'resident@example.com', 'MessageID' => 123456789],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Mail::to('resident@example.com')->send(new DocumentRequestRejected(
            'REQ-2026-99999',
            'Barangay Clearance',
            'Juan Dela Cruz',
            'Employment',
            'Uploaded valid ID is blurry.'
        ));

        Http::assertSent(function ($request) {
            $expectedAuth = 'Basic ' . base64_encode('mj_test_api_key:mj_test_secret_key');
            $message = $request['Messages'][0] ?? [];

            return $request->url() === 'https://api.mailjet.com/v3.1/send'
                && $request->hasHeader('Authorization', $expectedAuth)
                && ($message['From']['Email'] ?? null) === 'susontroy5@gmail.com'
                && ($message['To'][0]['Email'] ?? null) === 'resident@example.com'
                && str_contains((string) ($message['Subject'] ?? ''), 'REQ-2026-99999')
                && str_contains((string) ($message['HTMLPart'] ?? ''), 'Uploaded valid ID is blurry.');
        });
    }

    public function test_rejecting_document_request_delivers_email_via_mailjet_http_and_notifies_portal(): void
    {
        Config::set('mail.default', 'mailjet');
        Config::set('mail.from.address', 'susontroy5@gmail.com');
        Config::set('mail.from.name', 'E-Barangay Cogon East');
        Config::set('mail.mailers.smtp.username', 'mj_fallback_key');
        Config::set('mail.mailers.smtp.password', 'mj_fallback_secret');
        Config::set('mail.mailers.mailjet.key', null);
        Config::set('mail.mailers.mailjet.secret', null);
        Config::set('services.mailjet.key', null);
        Config::set('services.mailjet.secret', null);
        Mail::purge('mailjet');

        Http::fake([
            'https://api.mailjet.com/v3.1/send' => Http::response([
                'Messages' => [['Status' => 'success']],
            ], 200),
        ]);

        $admin = User::create([
            'user_id' => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name' => 'Barangay Admin',
            'first_name' => 'Barangay',
            'last_name' => 'Admin',
            'email' => 'admin_mj_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'Admin',
            'status' => 'active',
        ]);

        $resident = User::create([
            'user_id' => 'RES-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99),
            'name' => 'Troy Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'resident_mj_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $docRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-MJ-' . rand(1000, 9999),
            'first_name' => 'TROY',
            'last_name' => 'SUSON',
            'resident_email' => $resident->email,
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Employment',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($admin)->patchJson(route('admin.requests.reject', $docRequest->id), [
            'reason' => 'Incomplete requirements.',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'email_sent' => true,
                'portal_notified' => true,
            ]);

        $this->assertDatabaseMissing('document_requests', ['id' => $docRequest->id]);
        Http::assertSent(function ($request) use ($resident) {
            $message = $request['Messages'][0] ?? [];

            return $request->url() === 'https://api.mailjet.com/v3.1/send'
                && ($message['To'][0]['Email'] ?? null) === $resident->email
                && str_contains((string) ($message['HTMLPart'] ?? ''), 'Incomplete requirements.');
        });
    }

    public function test_mailjet_http_transport_throws_when_credentials_missing_or_api_fails(): void
    {
        Config::set('mail.default', 'mailjet');
        Config::set('mail.mailers.mailjet.key', '');
        Config::set('mail.mailers.mailjet.secret', '');
        Config::set('mail.mailers.smtp.username', '');
        Config::set('mail.mailers.smtp.password', '');
        Config::set('services.mailjet.key', '');
        Config::set('services.mailjet.secret', '');
        Mail::purge('mailjet');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mailjet API credentials');

        Mail::to('resident@example.com')->send(new DocumentRequestRejected(
            'REQ-2026-00001',
            'Barangay Clearance',
            'Resident'
        ));
    }

    public function test_mailjet_http_transport_throws_on_api_error_response(): void
    {
        Config::set('mail.default', 'mailjet');
        Config::set('mail.from.address', 'susontroy5@gmail.com');
        Config::set('mail.mailers.mailjet.key', 'bad_key');
        Config::set('mail.mailers.mailjet.secret', 'bad_secret');
        Mail::purge('mailjet');

        Http::fake([
            'https://api.mailjet.com/v3.1/send' => Http::response([
                'ErrorMessage' => 'API key authentication/authorization failure.',
            ], 401),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mailjet API returned error 401');

        Mail::to('resident@example.com')->send(new DocumentRequestRejected(
            'REQ-2026-00002',
            'Barangay Clearance',
            'Resident'
        ));
    }
}
