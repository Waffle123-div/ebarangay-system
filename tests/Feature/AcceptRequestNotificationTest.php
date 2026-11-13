<?php

namespace Tests\Feature;

use App\Mail\DocumentRequestAccepted;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Notifications\DocumentAcceptedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AcceptRequestNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);

        $this->admin = User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Barangay Admin',
            'first_name'        => 'Barangay',
            'last_name'         => 'Admin',
            'email'             => 'admin_accept_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);
    }

    public function test_accepting_document_request_sends_email_and_notifies_resident_portal(): void
    {
        Mail::fake();

        $resident = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99),
            'name'              => 'Troy Suson',
            'first_name'        => 'Troy',
            'last_name'         => 'Suson',
            'email'             => 'troy_accept_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin'      => bcrypt('1234'),
            'password'          => bcrypt('Password123!'),
            'role'              => 'resident',
            'status'            => 'active',
        ]);

        $docRequest = DocumentRequest::create([
            'user_id'         => $resident->id,
            'tracking_number' => 'REQ-ACC-' . rand(1000, 9999),
            'first_name'      => 'TROY',
            'last_name'       => 'SUSON',
            'resident_email'  => $resident->email,
            'document_type'   => 'Certification for Senior Citizen',
            'purpose'         => 'SCHOLARSHIP',
            'fee'             => 0,
            'status'          => 'Pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.requests.accept', $docRequest->id));

        $response->assertOk()
            ->assertJson([
                'success'         => true,
                'status'          => 'Processing',
                'email_sent'      => true,
                'portal_notified' => true,
            ]);

        $this->assertDatabaseHas('document_requests', [
            'id'     => $docRequest->id,
            'status' => 'Processing',
        ]);

        // Assert email sent to resident
        Mail::assertSent(DocumentRequestAccepted::class, function (DocumentRequestAccepted $mail) use ($resident, $docRequest) {
            return $mail->hasTo($resident->email)
                && $mail->documentRequest->id === $docRequest->id;
        });

        // Assert mailable renders expected request details
        $mailableHtml = (new DocumentRequestAccepted($docRequest->fresh()))->render();
        $this->assertStringContainsString($docRequest->tracking_number, $mailableHtml);
        $this->assertStringContainsString('Certification for Senior Citizen', $mailableHtml);
        $this->assertStringContainsString('Processing', $mailableHtml);

        // Assert resident database notification created for portal bell
        $notification = $resident->fresh()->unreadNotifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame(DocumentAcceptedNotification::class, $notification->type);
        $this->assertSame($docRequest->tracking_number, $notification->data['tracking_number']);
        $this->assertSame('Processing', $notification->data['status']);
        $this->assertStringContainsString('accepted and is now being processed', $notification->data['message']);

        // Assert resident notifications AJAX endpoint serves it cleanly
        $notifApiResponse = $this->actingAs($resident)
            ->getJson(route('resident.notifications.get'));

        $notifApiResponse->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.tracking_number', $docRequest->tracking_number)
            ->assertJsonPath('notifications.0.status', 'Processing');

        // Assert resident dashboard renders the portal notification
        $dashboardResponse = $this->actingAs($resident)->get(route('resident.dashboard'));
        $dashboardResponse->assertOk()
            ->assertSee($docRequest->tracking_number)
            ->assertSee('accepted and is now being processed');
    }

    public function test_accepting_request_falls_back_to_user_email_and_avoids_duplicate_notifications(): void
    {
        Mail::fake();

        $resident = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99),
            'name'              => 'Ana Reyes',
            'first_name'        => 'Ana',
            'last_name'         => 'Reyes',
            'email'             => 'ana_fallback_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin'      => bcrypt('1234'),
            'password'          => bcrypt('Password123!'),
            'role'              => 'resident',
            'status'            => 'active',
        ]);

        $docRequest = DocumentRequest::create([
            'user_id'         => $resident->id,
            'tracking_number' => 'REQ-FBK-' . rand(1000, 9999),
            'first_name'      => 'ANA',
            'last_name'       => 'REYES',
            'resident_email'  => null,
            'document_type'   => 'Barangay Clearance',
            'purpose'         => 'EMPLOYMENT',
            'status'          => 'Under Review',
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.requests.accept', $docRequest->id))
            ->assertOk()
            ->assertJson([
                'success'         => true,
                'status'          => 'Processing',
                'email_sent'      => true,
                'portal_notified' => true,
            ]);

        // Re-accepting an already-Processing request must not send duplicate emails or notifications
        $this->actingAs($this->admin)
            ->patchJson(route('admin.requests.accept', $docRequest->id))
            ->assertOk();

        Mail::assertSent(DocumentRequestAccepted::class, 1);
        Mail::assertSent(DocumentRequestAccepted::class, fn ($mail) => $mail->hasTo($resident->email));
        $this->assertCount(1, $resident->fresh()->unreadNotifications);
    }

    public function test_accepting_request_still_succeeds_and_notifies_portal_when_mail_throws(): void
    {
        $resident = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)) . rand(10, 99),
            'name'              => 'Pedro Cruz',
            'first_name'        => 'Pedro',
            'last_name'         => 'Cruz',
            'email'             => 'pedro_smtpfail_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin'      => bcrypt('1234'),
            'password'          => bcrypt('Password123!'),
            'role'              => 'resident',
            'status'            => 'active',
        ]);

        $docRequest = DocumentRequest::create([
            'user_id'         => $resident->id,
            'tracking_number' => 'REQ-ERR-' . rand(1000, 9999),
            'first_name'      => 'PEDRO',
            'last_name'       => 'CRUZ',
            'resident_email'  => $resident->email,
            'document_type'   => 'Certificate of Indigency',
            'purpose'         => 'MEDICAL ASSISTANCE',
            'status'          => 'Pending',
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with($resident->email)
            ->andThrow(new \RuntimeException('SMTP connection refused'));

        $response = $this->actingAs($this->admin)
            ->patchJson(route('admin.requests.accept', $docRequest->id));

        $response->assertOk()
            ->assertJson([
                'success'         => true,
                'status'          => 'Processing',
                'email_sent'      => false,
                'portal_notified' => true,
            ]);

        $this->assertDatabaseHas('document_requests', [
            'id'     => $docRequest->id,
            'status' => 'Processing',
        ]);
        $this->assertCount(1, $resident->fresh()->unreadNotifications);
    }
}
