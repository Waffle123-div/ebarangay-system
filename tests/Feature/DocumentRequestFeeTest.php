<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DocumentRequestFeeTest extends TestCase
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

    public function test_receipt_displays_to_be_settled_when_fee_is_null(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6),
            'name' => 'Troy Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'troy_fee_test_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $request = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'document_type' => 'Acknowledgement Receipt',
            'purpose' => 'Government Requirement',
            'status' => 'Pending',
            'fee' => null,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.receipt.view', $request->id));

        $response->assertStatus(200);
        $response->assertSee('TO BE SETTLED');
    }

    public function test_admin_can_update_fee_and_receipt_shows_amount(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-' . substr(uniqid(), -6),
            'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_fee_test_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6),
            'name' => 'Troy Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'resident_fee_test_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $request = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'document_type' => 'Acknowledgement Receipt',
            'purpose' => 'Government Requirement',
            'status' => 'Pending',
            'fee' => null,
        ]);

        // Admin updates fee to 75.00
        $response = $this->actingAs($admin)->patch(route('admin.requests.updateFee', $request->id), [
            'fee' => '75.00',
        ]);

        $response->assertRedirect();
        $this->assertEquals(75.00, (float)$request->fresh()->fee);

        // Resident checks receipt and sees updated amount
        $receiptResponse = $this->actingAs($resident)->get(route('resident.receipt.view', $request->id));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('75.00');
        $receiptResponse->assertDontSee('TO BE SETTLED');
    }

    public function test_admin_can_update_fee_via_edit_request_form(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-' . substr(uniqid(), -6),
            'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_edit_test_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6),
            'name' => 'Troy Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'resident_edit_test_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $request = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'document_type' => 'Acknowledgement Receipt',
            'purpose' => 'Government Requirement',
            'status' => 'Pending',
            'age' => 22,
            'fee' => null,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.requests.update', $request->id), [
            'first_name' => 'Troy Updated',
            'last_name' => 'Suson',
            'middle_initial' => 'C',
            'age' => 23,
            'document_type' => 'Certificate of Residency',
            'purpose' => 'Employment requirement',
            'resident_email' => 'resident_edit_test@example.com',
            'contact_number' => '09123456789',
            'fee' => '120.50',
        ]);

        $response->assertRedirect();
        $updated = $request->fresh();
        $this->assertEquals('TROY UPDATED', $updated->first_name);
        $this->assertEquals(23, $updated->age);
        $this->assertEquals(120.50, (float)$updated->fee);

        // Resident checks receipt and sees 120.50
        $receiptResponse = $this->actingAs($resident)->get(route('resident.receipt.view', $request->id));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('120.50');
        $receiptResponse->assertDontSee('TO BE SETTLED');
    }
}
