<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiDocumentRequestTest extends TestCase
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

    public function test_resident_can_submit_single_document_request(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Juan Dela Cruz',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan_single_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $response = $this->actingAs($resident)->post(route('resident.request.store'), [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'age' => 28,
            'contact_number' => '09123456789',
            'resident_email' => $resident->email,
            'document_type' => 'Certificate of Residency',
            'purpose' => 'BANK ACCOUNT OPENING',
        ]);

        $response->assertRedirect(route('resident.dashboard'));
        $response->assertSessionHas('success');

        $created = DocumentRequest::where('resident_email', $resident->email)->latest()->first();
        $this->assertNotNull($created);
        $this->assertFalse($created->hasMultipleDocuments());
        $this->assertCount(1, $created->documents_list);
        $this->assertEquals('Certificate of Residency', $created->document_type);
        $this->assertEquals('BANK ACCOUNT OPENING', $created->purpose);
    }

    public function test_receipt_shows_single_document_request(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Maria Clara',
            'first_name' => 'Maria',
            'last_name' => 'Clara',
            'email' => 'maria_single_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $request = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name' => 'Maria',
            'last_name' => 'Clara',
            'resident_email' => $resident->email,
            'document_type' => 'Certificate of Residency',
            'purpose' => 'SCHOLARSHIP APPLICATION',
            'documents' => [
                ['type' => 'Certificate of Residency', 'purpose' => 'SCHOLARSHIP APPLICATION'],
            ],
            'status' => 'Pending',
            'fee' => 50.00,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.receipt.view', $request->id));

        $response->assertStatus(200);
        $response->assertSee('Certificate of Residency');
        $response->assertSee('SCHOLARSHIP APPLICATION');
        $response->assertSee('50.00');
    }

    public function test_admin_requests_table_displays_single_document_request(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Admin Controller',
            'first_name' => 'Admin',
            'last_name' => 'Controller',
            'email' => 'admin_ctrl_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Pedro Penduko',
            'first_name' => 'Pedro',
            'last_name' => 'Penduko',
            'email' => 'pedro_single_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $request = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name' => 'Pedro',
            'last_name' => 'Penduko',
            'document_type' => 'Certificate of Residency',
            'purpose' => 'VALID ID',
            'documents' => [
                ['type' => 'Certificate of Residency', 'purpose' => 'VALID ID'],
            ],
            'attachments' => [
                [
                    'file_path' => 'residency_proofs/sample.jpg',
                    'file_name' => 'valid_id.png',
                    'doc_type'  => 'Valid Government ID',
                    'file_type' => 'image',
                    'file_size' => 123456,
                ]
            ],
            'status' => 'Pending',
            'fee' => 50.00,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.requests', ['status' => 'Pending_Review']));

        $response->assertStatus(200);
        $response->assertDontSee('2 Documents');
        $response->assertSee('Certificate of Residency');
        $response->assertSee('Attached Documents');
        $response->assertSee('Valid Government ID');
        $response->assertSee('valid_id.png');
    }

    public function test_admin_can_update_document_in_existing_request(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Admin Officer',
            'first_name' => 'Admin',
            'last_name' => 'Officer',
            'email' => 'admin_update_doc_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Ana Santos',
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'email' => 'ana_resident_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $request = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'resident_email' => $resident->email,
            'document_type' => 'Barangay Certificate',
            'purpose' => 'EMPLOYMENT',
            'status' => 'Pending',
            'fee' => null,
        ]);

        $this->assertFalse($request->hasMultipleDocuments());
        $this->assertCount(1, $request->documents_list);

        // Admin updates document to Certificate of Residency and purpose to BANK APPLICATION
        $response = $this->actingAs($admin)->patch(route('admin.requests.update', $request->id), [
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'resident_email' => $resident->email,
            'document_type' => 'Certificate of Residency',
            'purpose' => 'BANK APPLICATION',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $updated = $request->fresh();
        $this->assertFalse($updated->hasMultipleDocuments());
        $this->assertCount(1, $updated->documents_list);
        $this->assertEquals('Certificate of Residency', $updated->document_type);
        $this->assertEquals('BANK APPLICATION', $updated->purpose);
    }

    public function test_request_document_page_renders_successfully(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Troy C. Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'troy_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $response = $this->actingAs($resident)->get(route('resident.request.create'));
        $response->assertStatus(200);
        $response->assertSee('Submit Document Request');
        $response->assertSee('confirmSubmitModal');
        $response->assertSee('max-width: 780px', false);
        $response->assertSee('modal-lg');
        $response->assertSee('modalProcessingTime');
        $response->assertSee('attachment-preview-card');
        $response->assertSee('overflow-wrap: anywhere', false);
        $response->assertDontSee('id="summarySection"', false);
    }

    public function test_resident_can_submit_request_with_full_name_and_address(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Troy C. Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'troy_submit_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $response = $this->actingAs($resident)->post(route('resident.request.store'), [
            'full_name' => 'Troy C. Suson',
            'contact_number' => '09123456789',
            'address' => 'Purok Mangga, Carmen, Cebu',
            'document_type' => 'Certificate of Residency',
            'purpose' => 'BANK REQUIREMENTS',
        ]);

        $response->assertRedirect(route('resident.dashboard'));
        $response->assertSessionHas('success');

        $req = DocumentRequest::where('resident_email', $resident->email)->latest()->first();
        $this->assertNotNull($req);
        $this->assertEquals('TROY', $req->first_name);
        $this->assertEquals('SUSON', $req->last_name);
        $this->assertEquals('PUROK MANGGA', $req->purok);
        $this->assertCount(1, $req->documents_list);
        $this->assertEquals('Certificate of Residency', $req->document_type);
    }

    public function test_resident_can_submit_request_with_attachments(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Troy C. Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'troy_attach_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $docs = [
            ['type' => 'Certificate of Residency', 'purpose' => 'BANK REQUIREMENTS'],
        ];

        $file = \Illuminate\Http\UploadedFile::fake()->create('id_823.png', 150, 'image/png');

        $response = $this->actingAs($resident)->post(route('resident.request.store'), [
            'full_name' => 'Troy C. Suson',
            'contact_number' => '09123456789',
            'address' => 'Purok Mangga, Carmen, Cebu',
            'documents_json' => json_encode($docs),
            'attachments' => [$file],
            'attachment_meta' => json_encode([
                [
                    'name' => 'id_823.png',
                    'doc_type' => 'Valid Government ID',
                    'file_type' => 'image/png',
                    'size' => 150000,
                ]
            ]),
        ]);

        $response->assertRedirect(route('resident.dashboard'));
        $response->assertSessionHas('success');

        $req = DocumentRequest::where('resident_email', $resident->email)->latest()->first();
        $this->assertNotNull($req);
    }

    public function test_admin_request_details_displays_submitted_requirements_with_view(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Admin Officer',
            'first_name' => 'Admin',
            'last_name' => 'Officer',
            'email' => 'admin_id_type_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Troy Suson',
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'email' => 'troy_id_type_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
            'id_type' => 'National ID',
        ]);

        $docRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'document_type' => 'Acknowledgement Receipt',
            'id_type' => 'National ID',
            'attachments' => [
                [
                    'file_path' => 'attachments/national_id.png',
                    'file_name' => 'national_id.png',
                    'doc_type'  => 'Valid Government ID',
                    'file_type' => 'image',
                    'file_size' => 123456,
                ],
            ],
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.requests', ['status' => 'Pending_Review']));

        $response->assertStatus(200);
        $response->assertSee('Attached Documents &amp; Type', false);
        $response->assertSee('ID Type');
        $response->assertDontSee('Document Type &amp; ID Type', false);
        $response->assertSee('Acknowledgement Receipt');
        $response->assertSee('Valid Government ID');
        $response->assertSee('View');
        $response->assertSee('previewAdminAttachment');
    }

    public function test_admin_can_update_id_type_via_edit_request(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Admin Officer',
            'first_name' => 'Admin',
            'last_name' => 'Officer',
            'email' => 'admin_update_id_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $docRequest = DocumentRequest::create([
            'user_id' => $admin->id,
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'document_type' => 'Barangay Certificate',
            'id_type' => 'National ID',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.requests.update', $docRequest->id), [
            'first_name' => 'Troy',
            'last_name' => 'Suson',
            'document_type' => 'Barangay Certificate',
            'id_type' => "Driver's License",
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals("Driver's License", $docRequest->fresh()->id_type);
    }
}
