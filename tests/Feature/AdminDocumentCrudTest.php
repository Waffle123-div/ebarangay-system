<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\PhpWord;
use Tests\TestCase;

class AdminDocumentCrudTest extends TestCase
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

    private function createAdmin(): User
    {
        return User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Admin Officer',
            'first_name'        => 'Admin',
            'last_name'         => 'Officer',
            'email'             => 'admin_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin'      => bcrypt('1234'),
            'password'          => bcrypt('Password123!'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);
    }

    private function createResident(): User
    {
        return User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Juan Dela Cruz',
            'first_name'        => 'Juan',
            'last_name'         => 'Dela Cruz',
            'email'             => 'resident_' . uniqid() . '@example.com',
            'phone_number'      => '09' . rand(100000000, 999999999),
            'purok'             => 'Purok 1',
            'street'            => 'Main St',
            'age'               => 30,
            'id_type'           => 'National ID',
            'email_verified_at' => now(),
            'security_pin'      => bcrypt('1234'),
            'password'          => bcrypt('Password123!'),
            'role'              => 'Resident',
            'status'            => 'active',
        ]);
    }

    public function test_admin_can_view_document_types_catalog(): void
    {
        $admin = $this->createAdmin();
        DocumentType::firstOrCreate(
            ['name' => 'Barangay Clearance'],
            ['fee' => 50.00, 'processing_days' => '1-2 Days', 'is_active' => true]
        );

        $response = $this->actingAs($admin)->get(route('admin.document-types.index'));

        $response->assertStatus(200);
        $response->assertSee('Document Types Catalog');
        $response->assertSee('Barangay Clearance');
    }

    public function test_admin_can_create_document_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.document-types.store'), [
            'name'            => 'Barangay ID Card ' . uniqid(),
            'fee'             => 75.00,
            'processing_days' => 'Same Day',
            'description'     => 'Official Barangay ID Card with QR verification',
            'requirements'    => "Valid Government ID\n1x1 Photo\nProof of Residency",
            'is_active'       => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('document_types', [
            'fee' => 75.00,
            'processing_days' => 'Same Day',
        ]);
    }

    public function test_admin_can_update_document_type(): void
    {
        $admin = $this->createAdmin();

        $docType = DocumentType::create([
            'name'            => 'Test Clearance ' . uniqid(),
            'fee'             => 40.00,
            'processing_days' => '1 Day',
            'is_active'       => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.document-types.update', $docType->id), [
            'name'            => $docType->name,
            'fee'             => 80.00,
            'processing_days' => '3 Days',
            'description'     => 'Updated description',
            'requirements'    => "Updated Requirement 1\nUpdated Requirement 2",
            'is_active'       => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $docType->refresh();
        $this->assertEquals(80.00, (float)$docType->fee);
        $this->assertEquals('3 Days', $docType->processing_days);
    }

    public function test_admin_can_toggle_document_type_status(): void
    {
        $admin = $this->createAdmin();

        $docType = DocumentType::create([
            'name'            => 'Toggle Certificate ' . uniqid(),
            'fee'             => 25.00,
            'processing_days' => '1 Day',
            'is_active'       => true,
        ]);

        $this->actingAs($admin)->patch(route('admin.document-types.toggle-status', $docType->id));

        $docType->refresh();
        $this->assertFalse($docType->is_active);

        $this->actingAs($admin)->patch(route('admin.document-types.toggle-status', $docType->id));

        $docType->refresh();
        $this->assertTrue($docType->is_active);
    }

    public function test_admin_can_delete_unused_document_type(): void
    {
        $admin = $this->createAdmin();

        $docType = DocumentType::create([
            'name'            => 'Delete Certificate ' . uniqid(),
            'fee'             => 10.00,
            'processing_days' => '1 Day',
            'is_active'       => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.document-types.destroy', $docType->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('document_types', ['id' => $docType->id]);
    }

    public function test_admin_can_create_walk_in_request_for_registered_resident(): void
    {
        $admin = $this->createAdmin();
        $resident = $this->createResident();

        $response = $this->actingAs($admin)->post(route('admin.requests.walk-in'), [
            'resident_type'  => 'registered',
            'user_id'        => $resident->id,
            'document_type'  => 'Barangay Clearance',
            'purpose'        => 'WALK-IN EMPLOYMENT APPLICATION',
            'priority'       => 'Normal',
            'initial_status' => 'Processing',
            'fee'            => 50.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $created = DocumentRequest::where('user_id', $resident->id)->latest()->first();
        $this->assertNotNull($created);
        $this->assertEquals('Barangay Clearance', $created->document_type);
        $this->assertEquals('Processing', $created->status);
        $this->assertEquals(50.00, (float)$created->fee);
        $this->assertEquals('WALK-IN EMPLOYMENT APPLICATION', $created->purpose);
    }

    public function test_admin_can_create_walk_in_request_for_unregistered_resident(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.requests.walk-in'), [
            'resident_type'  => 'walkin',
            'first_name'     => 'Maria',
            'middle_initial' => 'C',
            'last_name'      => 'Santos',
            'age'            => 45,
            'contact_number' => '09987654321',
            'resident_email' => 'maria.santos@example.com',
            'purok'          => 'Purok Mangga',
            'street'         => 'Rizal Ave',
            'id_type'        => 'Driver\'s License',
            'document_type'  => 'Certificate of Indigency',
            'purpose'        => 'MEDICAL AID HOSPITAL',
            'priority'       => 'Urgent',
            'initial_status' => 'Ready',
            'waive_fee'      => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $created = DocumentRequest::where('first_name', 'MARIA')->where('last_name', 'SANTOS')->latest()->first();
        $this->assertNotNull($created);
        $this->assertNull($created->user_id);
        $this->assertEquals('Certificate of Indigency', $created->document_type);
        $this->assertEquals('Ready', $created->status);
        $this->assertEquals('Urgent', $created->priority);
        $this->assertEquals(0.00, (float)$created->fee);
    }

    public function test_resident_cannot_access_admin_document_types(): void
    {
        $resident = $this->createResident();

        $response = $this->actingAs($resident)->get(route('admin.document-types.index'));

        // AdminMiddleware redirects non-admins or aborts with 403
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_admin_can_view_paginated_requests(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.requests'));
        $response->assertStatus(200);
        $response->assertViewHas('allRequests');
    }

    public function test_admin_show_request_redirects_safely(): void
    {
        $admin = $this->createAdmin();

        // Non-existent request redirects to admin.requests with info flash
        $response = $this->actingAs($admin)->get('/admin/requests/999999');
        $response->assertRedirect(route('admin.requests'));
    }

    public function test_admin_can_bulk_delete_document_types(): void
    {
        $admin = $this->createAdmin();

        $doc1 = DocumentType::create([
            'name' => 'Bulk Test Doc 1 ' . uniqid(),
            'fee'  => 50,
            'processing_days' => '1 Day',
            'is_active' => true,
        ]);
        $doc2 = DocumentType::create([
            'name' => 'Bulk Test Doc 2 ' . uniqid(),
            'fee'  => 100,
            'processing_days' => '2 Days',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.document-types.bulk-delete'), [
            'ids' => [$doc1->id, $doc2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('document_types', ['id' => $doc1->id]);
        $this->assertDatabaseMissing('document_types', ['id' => $doc2->id]);
    }

    private function createValidDocxFile(string $content = 'Official Document for ${name}'): string
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText($content);
        $tempPath = tempnam(sys_get_temp_dir(), 'test_docx_') . '.docx';
        $phpWord->save($tempPath, 'Word2007');
        return $tempPath;
    }

    public function test_admin_can_create_document_type_with_custom_template(): void
    {
        $admin = $this->createAdmin();
        $docxPath = $this->createValidDocxFile('CUSTOM TEMPLATE CONTENT FOR ${name}');
        $uploaded = new UploadedFile(
            $docxPath,
            'custom_clearance.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $name = 'Custom Clearance ' . uniqid();
        $response = $this->actingAs($admin)->post(route('admin.document-types.store'), [
            'name'            => $name,
            'fee'             => 120.00,
            'processing_days' => '2 Days',
            'description'     => 'Clearance with uploaded Word template',
            'requirements'    => 'Valid ID',
            'is_active'       => 1,
            'template_file'   => $uploaded,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $docType = DocumentType::where('name', $name)->first();
        $this->assertNotNull($docType);
        $this->assertNotNull($docType->template_file);
        $this->assertStringContainsString('document_templates/', $docType->template_file);
        $this->assertFileExists(storage_path('app/' . $docType->template_file));

        if (file_exists(storage_path('app/' . $docType->template_file))) {
            @unlink(storage_path('app/' . $docType->template_file));
        }
        @unlink($docxPath);
    }

    public function test_admin_can_download_document_type_template(): void
    {
        $admin = $this->createAdmin();
        $docxPath = $this->createValidDocxFile('CUSTOM DOWNLOAD TEMPLATE');
        $destName = 'document_templates/test_dl_' . uniqid() . '.docx';
        @mkdir(storage_path('app/document_templates'), 0755, true);
        copy($docxPath, storage_path('app/' . $destName));

        $docType = DocumentType::create([
            'name'            => 'Downloadable Template Doc ' . uniqid(),
            'fee'             => 60.00,
            'processing_days' => '1 Day',
            'template_file'   => $destName,
            'is_active'       => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.document-types.download-template', $docType->id));
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');

        @unlink(storage_path('app/' . $destName));
        @unlink($docxPath);
    }

    public function test_admin_can_update_and_remove_document_type_template(): void
    {
        $admin = $this->createAdmin();
        $docxPath1 = $this->createValidDocxFile('TEMPLATE V1');
        $destName1 = 'document_templates/test_v1_' . uniqid() . '.docx';
        @mkdir(storage_path('app/document_templates'), 0755, true);
        copy($docxPath1, storage_path('app/' . $destName1));

        $docType = DocumentType::create([
            'name'            => 'Updatable Template Doc ' . uniqid(),
            'fee'             => 50.00,
            'processing_days' => '1 Day',
            'template_file'   => $destName1,
            'is_active'       => true,
        ]);

        // Upload replacement template
        $docxPath2 = $this->createValidDocxFile('TEMPLATE V2');
        $uploaded2 = new UploadedFile(
            $docxPath2,
            'replacement_template.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $response = $this->actingAs($admin)->patch(route('admin.document-types.update', $docType->id), [
            'name'            => $docType->name,
            'fee'             => 50.00,
            'template_file'   => $uploaded2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $docType->refresh();
        $this->assertNotEquals($destName1, $docType->template_file);
        $this->assertNotNull($docType->template_file);
        $this->assertFileExists(storage_path('app/' . $docType->template_file));

        $newFilePath = storage_path('app/' . $docType->template_file);

        // Remove template
        $removeResponse = $this->actingAs($admin)->patch(route('admin.document-types.update', $docType->id), [
            'name'            => $docType->name,
            'fee'             => 50.00,
            'remove_template' => 1,
        ]);

        $removeResponse->assertRedirect();
        $docType->refresh();
        $this->assertNull($docType->template_file);

        @unlink($docxPath1);
        @unlink($docxPath2);
        if (file_exists($newFilePath)) {
            @unlink($newFilePath);
        }
    }

    public function test_word_generation_uses_custom_template(): void
    {
        $admin = $this->createAdmin();
        $resident = $this->createResident();

        $docxPath = $this->createValidDocxFile('SPECIAL CUSTOM TEMPLATE FOR ${name} - REF ${tracking_number}');
        $destName = 'document_templates/test_gen_' . uniqid() . '.docx';
        @mkdir(storage_path('app/document_templates'), 0755, true);
        copy($docxPath, storage_path('app/' . $destName));

        $typeName = 'Custom Permit ' . uniqid();
        $docType = DocumentType::create([
            'name'            => $typeName,
            'fee'             => 100.00,
            'processing_days' => '1 Day',
            'template_file'   => $destName,
            'is_active'       => true,
        ]);

        $docRequest = DocumentRequest::create([
            'user_id'          => $resident->id,
            'document_type_id' => $docType->id,
            'document_type'    => $typeName,
            'first_name'       => 'Juan',
            'last_name'        => 'Dela Cruz',
            'age'              => 30,
            'purpose'          => 'TEST CUSTOM TEMPLATE GENERATION',
            'priority'         => 'Normal',
            'status'           => 'Processing',
            'tracking_number'  => 'TRK-' . strtoupper(uniqid()),
            'fee'              => 100.00,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.requests.generate', $docRequest->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        @unlink(storage_path('app/' . $destName));
        @unlink($docxPath);
    }

    public function test_admin_can_upload_docx_with_various_browser_mime_types(): void
    {
        $admin = $this->createAdmin();

        $docType = DocumentType::create([
            'name'            => 'Mime Test Doc ' . uniqid(),
            'fee'             => 50.00,
            'processing_days' => '1 Day',
            'is_active'       => true,
        ]);

        $docxPath = $this->createValidDocxFile('TEMPLATE CONTENT');
        // Simulate browser uploading docx with application/zip or application/octet-stream
        $uploaded = new UploadedFile(
            $docxPath,
            '01_Barangay_Clearance_General.docx',
            'application/zip',
            null,
            true
        );

        $response = $this->actingAs($admin)->patch(route('admin.document-types.update', $docType->id), [
            'name'            => $docType->name,
            'fee'             => 50.00,
            'template_file'   => $uploaded,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionDoesntHaveErrors();

        $docType->refresh();
        $this->assertNotNull($docType->template_file);
        $this->assertFileExists(storage_path('app/' . $docType->template_file));

        @unlink(storage_path('app/' . $docType->template_file));
        @unlink($docxPath);
    }

    public function test_word_generation_replaces_all_new_merge_tags(): void
    {
        $admin = $this->createAdmin();

        \App\Models\BarangaySetting::set('barangay_captain', 'Hon. Maria Clara', 'barangay');
        \App\Models\BarangaySetting::set('barangay_secretary', 'Crisostomo Ibarra', 'barangay');

        $templateText = 'Status: ${civil_status} | Secretary: ${secretary_name} | Captain: ${captain_name} | OR: ${or_number} | Amount: ₱${amount}';
        $docxPath = $this->createValidDocxFile($templateText);
        $destName = 'document_templates/test_tags_' . uniqid() . '.docx';
        @mkdir(storage_path('app/document_templates'), 0755, true);
        copy($docxPath, storage_path('app/' . $destName));

        $docType = DocumentType::create([
            'name'            => 'Test Tags Doc ' . uniqid(),
            'fee'             => 85.50,
            'processing_days' => '1 Day',
            'template_file'   => $destName,
            'is_active'       => true,
        ]);

        $resident = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Elias Salome',
            'first_name'        => 'Elias',
            'last_name'         => 'Salome',
            'civil_status'      => 'Married',
            'email'             => 'elias_' . uniqid() . '@example.com',
            'role'              => 'Resident',
            'status'            => 'active',
        ]);

        $tracking = 'REQ-TEST-' . uniqid();
        $expectedOr = str_replace('REQ', 'REC', $tracking);

        $docRequest = DocumentRequest::create([
            'user_id'          => $resident->id,
            'tracking_number'  => $tracking,
            'first_name'       => 'Elias',
            'last_name'        => 'Salome',
            'document_type_id' => $docType->id,
            'document_type'    => $docType->name,
            'fee'              => 85.50,
            'status'           => 'Processing',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.requests.generate', $docRequest->id));
        $response->assertStatus(200);

        // Inspect downloaded file contents
        $streamedContent = $response->streamedContent();
        $tempOutput = tempnam(sys_get_temp_dir(), 'out_docx_') . '.docx';
        file_put_contents($tempOutput, $streamedContent);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tempOutput) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->assertStringContainsString('Status: MARRIED', $xml);
        $this->assertStringContainsString('Secretary: CRISOSTOMO IBARRA', $xml);
        $this->assertStringContainsString('Captain: HON. MARIA CLARA', $xml);
        $this->assertStringContainsString("OR: {$expectedOr}", $xml);
        $this->assertStringContainsString('Amount: ₱85.50', $xml);

        @unlink($docxPath);
        @unlink(storage_path('app/' . $destName));
        @unlink($tempOutput);
    }

    public function test_admin_requests_modal_has_edit_button_and_can_update_request()
    {
        $admin = $this->createAdmin();
        $resident = $this->createResident();

        $docRequest = DocumentRequest::create([
            'user_id'          => $resident->id,
            'tracking_number'  => 'REQ-TEST-' . uniqid(),
            'first_name'       => 'Troy',
            'last_name'        => 'Suson',
            'middle_initial'   => '',
            'age'              => 25,
            'document_type'    => 'Barangay Certificate',
            'purpose'          => 'Initial Purpose',
            'status'           => 'Pending',
        ]);

        // 1. Check requests index page contains edit button and toggle controls
        $response = $this->actingAs($admin)->get(route('admin.requests'));
        $response->assertStatus(200);
        $response->assertSee('id="btn-edit-' . $docRequest->id . '"', false);
        $response->assertSee('onclick="toggleEditMode(' . $docRequest->id . ', true)"', false);
        $response->assertSee('id="btn-header-save-' . $docRequest->id . '"', false);
        $response->assertSee('id="btn-cancel-edit-' . $docRequest->id . '"', false);
        $response->assertSee('id="edit-mode-' . $docRequest->id . '"', false);
        $response->assertSee('id="edit-form-' . $docRequest->id . '"', false);
        $response->assertSee('id="btn-save-' . $docRequest->id . '"', false);

        // 2. Submit the edit form to update the request
        $updateResponse = $this->actingAs($admin)->patch(route('admin.requests.update', $docRequest->id), [
            'first_name'     => 'Troy',
            'last_name'      => 'Suson Updated',
            'middle_initial' => 'A',
            'age'            => 26,
            'document_type'  => 'Barangay Certificate',
            'purpose'        => 'EMPLOYMENT VERIFICATION',
            'contact_number' => '09123456789',
            'resident_email' => 'troysuson@example.com',
            'id_type'        => "Driver's License",
            'fee'            => 50.00,
        ]);

        $updateResponse->assertSessionHasNoErrors();
        $updateResponse->assertRedirect();

        $docRequest->refresh();
        $this->assertEquals('TROY', $docRequest->first_name);
        $this->assertEquals('SUSON UPDATED', $docRequest->last_name);
        $this->assertEquals('A', $docRequest->middle_initial);
        $this->assertEquals(26, $docRequest->age);
        $this->assertEquals('EMPLOYMENT VERIFICATION', $docRequest->purpose);
        $this->assertEquals("Driver's License", $docRequest->id_type);
        $this->assertEquals(50.00, (float)$docRequest->fee);
    }
}


