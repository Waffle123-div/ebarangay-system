<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminAccountApprovalsTest extends TestCase
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

    public function test_admin_can_view_uploaded_id_in_account_approvals()
    {
        $admin = User::where('role', 'Admin')->first();
        if (!$admin) {
            $admin = User::create([
                'user_id' => 'USR-ADMIN1',
                'name' => 'Admin Test',
                'email' => 'admin_test@example.com',
                'password' => bcrypt('admin123'),
                'role' => 'Admin',
                'status' => 'active',
            ]);
        }

        $pendingUser = User::where('email', 'susontroy5@gmail.com')->first();
        if (!$pendingUser) {
            $pendingUser = User::create([
                'user_id' => 'USR-PENDING1',
                'name' => 'Pending Resident',
                'email' => 'susontroy5@gmail.com',
                'password' => bcrypt('password123'),
                'role' => 'Resident',
                'status' => 'pending_approval',
                'proof_of_residency' => 'residency_proofs/sample.jpg',
            ]);
        } else {
            $pendingUser->update([
                'status' => 'pending_approval',
                'proof_of_residency' => $pendingUser->proof_of_residency ?: 'residency_proofs/sample.jpg',
            ]);
            $pendingUser->refresh();
        }

        $this->assertNotNull($pendingUser, 'Pending user susontroy5@gmail.com should exist');
        $this->assertNotEmpty($pendingUser->id_document_url, 'id_document_url should resolve proof_of_residency');

        $response = $this->actingAs($admin)->get(route('admin.approvals'));

        $response->assertStatus(200);
        $response->assertSee('Account Approvals');
        $response->assertSee('View ID');
        $response->assertSee($pendingUser->id_document_url, false);
        $response->assertSee('id="docPreviewModal"', false);
    }
}
