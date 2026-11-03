<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ResidentActivityLogsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);
    }

    public function test_resident_can_view_activity_logs_with_receipt_link(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Troy Test',
            'first_name' => 'Troy',
            'last_name' => 'Test',
            'email' => 'troy_log_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $docRequest = DocumentRequest::create([
            'tracking_number' => 'REQ-' . date('Y') . '-' . rand(10000, 99999),
            'user_id' => $resident->id,
            'first_name' => 'Troy',
            'last_name' => 'Test',
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Employment',
            'status' => 'Pending',
        ]);

        ActivityLog::create([
            'user_id' => $resident->id,
            'action' => 'Document Request',
            'description' => "Submitted request for Barangay Clearance (#{$docRequest->tracking_number})",
        ]);

        // Access activity logs page with verified session
        $response = $this->actingAs($resident)
            ->withSession(['pin_verified' => true])
            ->get(route('resident.activity-logs'));

        $response->assertStatus(200);
        $response->assertSee(route('resident.receipt.view', $docRequest->id));

        // Test that both resident.receipt.view and receipt.view resolve
        $this->assertEquals(
            route('resident.receipt.view', $docRequest->id),
            url('/resident/receipt/' . $docRequest->id)
        );

        $receiptResponse = $this->actingAs($resident)
            ->withSession(['pin_verified' => true])
            ->get(route('resident.receipt.view', $docRequest->id));

        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee($docRequest->tracking_number);

        // Alias receipt.view should also return 200
        $aliasResponse = $this->actingAs($resident)
            ->withSession(['pin_verified' => true])
            ->get(route('receipt.view', $docRequest->id));

        $aliasResponse->assertStatus(200);
        $aliasResponse->assertSee($docRequest->tracking_number);
    }

    public function test_resident_activity_logs_pagination_renders_cleanly(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Troy Pagination',
            'first_name' => 'Troy',
            'last_name' => 'Pagination',
            'email' => 'troy_pag_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        // Create 15 logs to trigger pagination (> 10 per page)
        for ($i = 1; $i <= 15; $i++) {
            ActivityLog::create([
                'user_id' => $resident->id,
                'action' => 'Activity ' . $i,
                'description' => "Test action description {$i}",
            ]);
        }

        $response = $this->actingAs($resident)
            ->withSession(['pin_verified' => true])
            ->get(route('resident.activity-logs'));

        $response->assertStatus(200);
        $response->assertSee('Showing 1–10 of 15 entries');
        $response->assertSee('pagination');
    }
}


