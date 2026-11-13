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
        $response->assertSee($pendingUser->id_document_url, false);
        $response->assertSee('id="docPreviewModal"', false);
        $response->assertSee('approval-row-clickable', false);
    }

    public function test_approval_selections_are_clickable_without_inline_action_buttons()
    {
        $admin = User::where('role', 'Admin')->first() ?? User::create([
            'user_id' => 'USR-' . rand(10000, 99999),
            'name' => 'Admin Click Test',
            'email' => 'admin_click_' . uniqid() . '@example.com',
            'password' => bcrypt('admin123'),
            'role' => 'Admin',
            'status' => 'active',
        ]);

        $pendingUser = User::create([
            'user_id' => 'USR-' . rand(10000, 99999),
            'name' => 'Pending Row Click Resident',
            'email' => 'pending_row_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Resident',
            'status' => 'pending_approval',
            'proof_of_residency' => 'residency_proofs/sample.jpg',
            'selfie_photo' => 'selfies/sample.jpg',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.approvals'));

        $response->assertStatus(200);

        // Clickable rows and cards
        $response->assertSee('approval-row-clickable');
        $response->assertSee('data-id="' . $pendingUser->id . '"', false);
        $response->assertSee('data-name="' . $pendingUser->name . '"', false);
        $response->assertSee('data-status="pending_approval"', false);

        // Inline table & card action buttons are removed
        $response->assertDontSee('>View ID & Face<', false);
        $response->assertDontSee('> Approve</button>', false);
        $response->assertDontSee('> Reject</button>', false);

        // The second picture (docPreviewModal) exists with action buttons placeholder
        $response->assertSee('id="docPreviewModal"', false);
        $response->assertSee('id="modalActionButtons"', false);
    }

    public function test_reject_action_uses_confirmation_modal_form_instead_of_browser_confirm()
    {
        $admin = User::where('role', 'Admin')->first() ?? User::create([
            'user_id' => 'USR-' . rand(10000, 99999),
            'name' => 'Admin Modal Test',
            'email' => 'admin_modal_' . uniqid() . '@example.com',
            'password' => bcrypt('admin123'),
            'role' => 'Admin',
            'status' => 'active',
        ]);

        $pendingUser = User::create([
            'user_id' => 'USR-' . rand(10000, 99999),
            'name' => 'Pending Reject Modal Resident',
            'email' => 'pending_reject_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Resident',
            'status' => 'pending_approval',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.approvals'));

        $response->assertStatus(200);
        $response->assertSee('id="rejectAccountConfirmModal"', false);
        $response->assertSee('id="rejectAccountConfirmForm"', false);
        $response->assertSee('id="openRejectAccountModalBtn"', false);
        $response->assertDontSee("confirm('Reject this account?')", false);

        \Illuminate\Support\Facades\Mail::fake();
        $rejectResponse = $this->actingAs($admin)->patch(route('admin.approvals.reject', $pendingUser->id));
        $rejectResponse->assertRedirect();

        $pendingUser->refresh();
        $this->assertSame('rejected', $pendingUser->status);
    }
}
