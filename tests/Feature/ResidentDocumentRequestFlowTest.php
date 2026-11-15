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

    public function test_resident_uploaded_request_image_is_stored_on_cloud_disk_and_visible_to_admin(): void
    {
        \Illuminate\Support\Facades\Storage::fake('s3');
        config([
            'filesystems.default' => 's3',
            'filesystems.disks.s3.bucket' => 'ebarangay_system_production',
        ]);

        $admin = User::create([
            'user_id' => 'ADM-' . rand(1000, 9999),
            'name' => 'Admin Reviewer',
            'email' => 'admin_req_view_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Troy Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'troy_cloud_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $createPage = $this->actingAs($resident)->get(route('resident.request.create'));
        $createPage->assertOk();
        $createPage->assertSee("formData.delete('attachments[]');", false);
        $createPage->assertSee('const pickedFiles = Array.from(this.files || []);', false);

        $image = \Illuminate\Http\UploadedFile::fake()->image('uploaded_picture.jpg', 200, 200);

        $this->actingAs($resident)
            ->post(route('resident.request.store'), [
                'first_name' => 'Troy',
                'last_name' => 'Suson',
                'age' => 22,
                'contact_number' => '09123456789',
                'resident_email' => $resident->email,
                'address' => 'Barangay Proper',
                'document_type' => 'Barangay Clearance',
                'purpose' => 'EMPLOYMENT REQUIREMENT',
                'attachments' => [$image],
                'attachment_meta' => json_encode([
                    [
                        'name' => 'uploaded_picture.jpg',
                        'doc_type' => 'Supporting Document',
                        'file_type' => 'image/jpeg',
                        'size' => $image->getSize(),
                    ],
                ]),
            ])
            ->assertRedirect();

        $docRequest = \App\Models\DocumentRequest::where('user_id', $resident->id)->latest('id')->firstOrFail();
        $attachments = $docRequest->attachments_list;

        $this->assertCount(1, $attachments);
        $this->assertSame('uploaded_picture.jpg', $attachments[0]['file_name']);
        $this->assertSame('image', $attachments[0]['file_type']);
        \Illuminate\Support\Facades\Storage::disk('s3')->assertExists($attachments[0]['file_path']);

        $previewUrl = route('admin.requests.attachment', [
            'documentRequest' => $docRequest->id,
            'index' => 0,
            'filename' => basename($attachments[0]['file_path']),
        ]);

        $this->actingAs($admin)->get(route('admin.requests'))
            ->assertOk()
            ->assertSee($previewUrl, false);

        $stream = $this->actingAs($admin)->get($previewUrl);
        $stream->assertOk();
        $this->assertStringContainsString('inline', (string) $stream->headers->get('Content-Disposition'));
    }
}
