<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ResidentDocumentRequestFlowTest extends TestCase
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

    public function test_resident_request_document_page_renders_multi_step_modal_panels_and_stepper(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Maria Santos',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria_steps_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $response = $this->actingAs($resident)->get(route('resident.request.create'));
        $response->assertOk();

        // Assert Step 2 and Step 3 panels exist for both BP and Generic modals
        $response->assertSee('id="bpStep2Panel"', false);
        $response->assertSee('id="bpStep3Panel"', false);
        $response->assertSee('id="genericStep2Panel"', false);
        $response->assertSee('id="genericStep3Panel"', false);

        // Assert navigation buttons exist
        $response->assertSee('id="bpBtnToStep3"', false);
        $response->assertSee('id="bpBtnBackToStep2"', false);
        $response->assertSee('id="bpModalConfirm"', false);
        $response->assertSee('id="genericBtnToStep3"', false);
        $response->assertSee('id="genericBtnBackToStep2"', false);
        $response->assertSee('id="genericModalConfirm"', false);

        // Assert Step 3 title & kicker matching user screenshot
        $response->assertSee('STEP 3:</span> Supporting Documents &amp; Upload', false);

        // Assert key JavaScript helper functions are present in the view script
        $response->assertSee('function showModalStep(', false);
        $response->assertSee('function proceedToReviewFromModal(', false);
        $response->assertSee('function openConfirmModal()', false);
        $response->assertSee('window.reopenUploadStep', false);
        $response->assertSee('window.changeDocumentFromReview', false);
    }

    public function test_resident_can_submit_document_request_with_attachments_and_metadata(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Troy Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'troy_flow_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('proof_of_residency.pdf', 120, 'application/pdf');

        $response = $this->actingAs($resident)->post(route('resident.request.store'), [
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'age' => 22,
            'contact_number' => '09123456789',
            'resident_email' => $resident->email,
            'address' => 'Barangay Proper',
            'document_type' => 'Barangay Clearance',
            'purpose' => 'EMPLOYMENT REQUIREMENT',
            'id_type' => 'National ID',
            'attachments' => [$file],
            'attachment_metadata' => json_encode([
                [
                    'name' => 'proof_of_residency.pdf',
                    'doc_type' => 'National ID',
                    'file_type' => '.pdf',
                    'size' => 122880,
                ],
            ]),
        ]);

        $response->assertRedirect(route('resident.request.track'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('document_requests', [
            'user_id' => $resident->id,
            'document_type' => 'Barangay Clearance',
            'purpose' => 'EMPLOYMENT REQUIREMENT',
            'id_type' => 'National ID',
        ]);
    }
}
