<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BarangaySetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
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
            'email_verified_at' => now(),
            'security_pin'      => bcrypt('1234'),
            'password'          => bcrypt('Password123!'),
            'role'              => 'Resident',
            'status'            => 'active',
        ]);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('Settings');
        $response->assertSee('Officials');
        $response->assertDontSee('Payments & GCash');
        $response->assertSee('Document Policies');
        $response->assertDontSee('Residents & Puroks');
        $response->assertSee('Public Announcement Banner');
        $response->assertSee('Disaster Recovery');
    }

    public function test_resident_cannot_access_admin_settings(): void
    {
        $resident = $this->createResident();

        $response = $this->actingAs($resident)->get(route('admin.settings'));

        $response->assertRedirect(route('resident.dashboard'));
    }

    public function test_admin_can_update_barangay_info(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.settings.barangay'), [
            '_group'                 => 'barangay',
            'barangay_name'          => 'Barangay Kapitolyo',
            'municipality'           => 'Pasig City',
            'province'               => 'Metro Manila',
            'region'                 => 'NCR',
            'barangay_captain'       => 'Hon. Captain Test',
            'barangay_secretary'     => 'Secretary Test',
            'barangay_treasurer'     => 'Treasurer Test',
            'barangay_hall_address'  => '123 Main Street',
            'official_contact'       => '09123456789',
            'emergency_hotline'      => '911',
            'official_email'         => 'kapitolyo@example.com',
            'office_hours'           => '8:00 AM - 5:00 PM',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('Barangay Kapitolyo', BarangaySetting::get('barangay_name'));
        $this->assertEquals('Hon. Captain Test', BarangaySetting::get('barangay_captain'));
    }

    public function test_admin_can_update_document_policies(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.settings.barangay'), [
            '_group'                            => 'documents',
            'default_processing_days'          => '3',
            'tracking_prefix'                  => 'KAP-DOC',
            'max_active_requests_per_resident' => '5',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('3', BarangaySetting::get('default_processing_days'));
        $this->assertEquals('KAP-DOC', BarangaySetting::get('tracking_prefix'));
        $this->assertEquals('5', BarangaySetting::get('max_active_requests_per_resident'));
    }

    public function test_admin_can_update_announcement_banner(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.settings.barangay'), [
            '_group'                      => 'announcements',
            'announcement_enabled'        => '1',
            'announcement_type'           => 'danger',
            'announcement_category'       => 'Weather & Calamity Alert',
            'announcement_title'          => 'Typhoon Advisory Signal 2',
            'announcement_message'        => 'All residents in flood-prone areas please prepare to evacuate.',
            'announcement_action_text'    => 'View Hotlines',
            'announcement_action_url'     => 'https://example.com/hotlines',
            'announcement_is_dismissible' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('1', BarangaySetting::get('announcement_enabled'));
        $this->assertEquals('danger', BarangaySetting::get('announcement_type'));
        $this->assertEquals('Weather & Calamity Alert', BarangaySetting::get('announcement_category'));
        $this->assertEquals('Typhoon Advisory Signal 2', BarangaySetting::get('announcement_title'));
        $this->assertEquals('View Hotlines', BarangaySetting::get('announcement_action_text'));
    }

    public function test_resident_dashboard_renders_active_announcement_banner(): void
    {
        $resident = $this->createResident();

        BarangaySetting::set('announcement_enabled', '1', 'announcements');
        BarangaySetting::set('announcement_type', 'danger', 'announcements');
        BarangaySetting::set('announcement_category', 'Urgent Advisory', 'announcements');
        BarangaySetting::set('announcement_title', 'Emergency Water Interruption Notice', 'announcements');
        BarangaySetting::set('announcement_message', 'Water service interruption scheduled from 10 PM to 4 AM.', 'announcements');
        BarangaySetting::set('announcement_action_text', 'View Affected Zones', 'announcements');
        BarangaySetting::set('announcement_action_url', 'https://example.com/zones', 'announcements');

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Emergency Water Interruption Notice');
        $response->assertSee('Urgent Advisory');
        $response->assertSee('Water service interruption scheduled from 10 PM to 4 AM.');
        $response->assertSee('View Affected Zones');

        // When disabled, banner should not display
        BarangaySetting::set('announcement_enabled', '0', 'announcements');
        $responseDisabled = $this->actingAs($resident)->get(route('resident.dashboard'));
        $responseDisabled->assertDontSee('Emergency Water Interruption Notice');
    }

    public function test_admin_can_download_database_backup(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.settings.backup-db'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/sql');
        $this->assertStringContainsString('E-Barangay System Database Backup', $response->getContent());
    }

    public function test_admin_can_generate_word_document_for_standard_request(): void
    {
        $admin = $this->createAdmin();

        $docRequest = \App\Models\DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name'      => 'Maria',
            'last_name'       => 'Santos',
            'document_type'   => 'Barangay Clearance',
            'purpose'         => 'Local Employment',
            'status'          => 'Processing',
            'age'             => 28,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.requests.generate', $docRequest->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $docRequest->refresh();
        $this->assertTrue((bool)$docRequest->is_generated);
    }

    public function test_admin_can_generate_word_document_for_custom_document_type(): void
    {
        $admin = $this->createAdmin();

        $docRequest = \App\Models\DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name'      => 'Jose',
            'last_name'       => 'Rizal',
            'document_type'   => 'Custom Barangay Certificate',
            'purpose'         => 'Scholarship Verification',
            'status'          => 'Pending',
            'age'             => 35,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.requests.generate', $docRequest->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $docRequest->refresh();
        $this->assertEquals('Processing', $docRequest->status);
        $this->assertTrue((bool)$docRequest->is_generated);
    }

    public function test_admin_can_generate_word_documents_as_zip_for_multi_doc_request(): void
    {
        $admin = $this->createAdmin();

        $docRequest = \App\Models\DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name'      => 'Emilio',
            'last_name'       => 'Aguinaldo',
            'document_type'   => 'Certificate of Residency, Barangay Certificate',
            'documents'       => [
                ['type' => 'Certificate of Residency', 'purpose' => 'Bank Application'],
                ['type' => 'Barangay Certificate', 'purpose' => 'Business Requirement'],
            ],
            'status'          => 'Processing',
            'age'             => 42,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.requests.generate', $docRequest->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
    }

    public function test_admin_can_generate_single_document_from_multi_doc_request(): void
    {
        $admin = $this->createAdmin();

        $docRequest = \App\Models\DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'first_name'      => 'Apolinario',
            'last_name'       => 'Mabini',
            'document_type'   => 'Certificate of Residency, Barangay Certificate',
            'documents'       => [
                ['type' => 'Certificate of Residency', 'purpose' => 'Bank Application'],
                ['type' => 'Barangay Certificate', 'purpose' => 'Legal Verification'],
            ],
            'status'          => 'Processing',
            'age'             => 39,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.requests.generate', [
            'id'       => $docRequest->id,
            'doc_type' => 'Barangay Certificate',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }
}
