<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DynamicDocumentTypeSelectionTest extends TestCase
{
    use DatabaseTransactions;

    protected array $createdDocTypeNames = [];

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdDocTypeNames)) {
            DocumentRequest::whereIn('document_type', $this->createdDocTypeNames)->delete();
            DocumentType::whereIn('name', $this->createdDocTypeNames)->delete();
        }
        parent::tearDown();
    }

    private function createAdmin(): User
    {
        return User::create([
            'user_id'           => 'ADM-' . substr(uniqid(), -6) . rand(10, 99),
            'name'              => 'Admin Officer',
            'first_name'        => 'Admin',
            'last_name'         => 'Officer',
            'email'             => 'admin_dyn_' . uniqid() . '@example.com',
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
            'user_id'           => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name'              => 'Juan Dela Cruz',
            'first_name'        => 'Juan',
            'last_name'         => 'Dela Cruz',
            'email'             => 'res_dyn_' . uniqid() . '@example.com',
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

    public function test_added_document_type_in_admin_appears_in_resident_request_selection(): void
    {
        $admin = $this->createAdmin();
        $resident = $this->createResident();
        $uniqueDocName = 'Barangay Solar Permit ' . uniqid();
        $this->createdDocTypeNames[] = $uniqueDocName;

        // Admin adds new document
        $response = $this->actingAs($admin)->post(route('admin.document-types.store'), [
            'name'            => $uniqueDocName,
            'fee'             => 150.00,
            'processing_days' => '3 Business Days',
            'description'     => 'Clearance for rooftop solar panel installations',
            'requirements'    => "Valid Government ID\nElectrical Plan\nRoof Inspection",
            'is_active'       => 1,
        ]);
        $response->assertRedirect();

        // Resident views request document page
        $resResponse = $this->actingAs($resident)->get(route('resident.request.create'));
        $resResponse->assertStatus(200);
        $resResponse->assertSee($uniqueDocName);
        $resResponse->assertSee('₱150.00');
        $resResponse->assertSee('Electrical Plan');
    }

    public function test_updated_document_type_in_admin_updates_details_in_resident_request_selection(): void
    {
        $admin = $this->createAdmin();
        $resident = $this->createResident();
        $uniqueDocName = 'Special Event Permit ' . uniqid();
        $this->createdDocTypeNames[] = $uniqueDocName;

        $docType = DocumentType::create([
            'name'            => $uniqueDocName,
            'fee'             => 50.00,
            'processing_days' => '1 Day',
            'requirements'    => ['Valid Government ID'],
            'is_active'       => true,
        ]);

        // Resident checks initial view
        $resResponse1 = $this->actingAs($resident)->get(route('resident.request.create'));
        $resResponse1->assertSee($uniqueDocName);
        $resResponse1->assertSee('₱50.00');

        // Admin updates fee and processing days
        $this->actingAs($admin)->patch(route('admin.document-types.update', $docType->id), [
            'name'            => $uniqueDocName,
            'fee'             => 250.00,
            'processing_days' => '5 Business Days',
            'description'     => 'Updated event permit description',
            'requirements'    => "Valid Government ID\nVenue Permit",
            'is_active'       => 1,
        ]);

        // Resident checks updated view
        $resResponse2 = $this->actingAs($resident)->get(route('resident.request.create'));
        $resResponse2->assertStatus(200);
        $resResponse2->assertSee($uniqueDocName);
        $resResponse2->assertSee('₱250.00');
        $resResponse2->assertSee('Venue Permit');
    }

    public function test_deactivated_or_deleted_document_type_is_removed_from_resident_selection(): void
    {
        $admin = $this->createAdmin();
        $resident = $this->createResident();
        $uniqueDocName = 'Temporary Gate Pass ' . uniqid();
        $this->createdDocTypeNames[] = $uniqueDocName;

        $docType = DocumentType::create([
            'name'            => $uniqueDocName,
            'fee'             => 20.00,
            'processing_days' => 'Same Day',
            'is_active'       => true,
        ]);

        // Visible initially
        $resResponse1 = $this->actingAs($resident)->get(route('resident.request.create'));
        $resResponse1->assertSee($uniqueDocName);

        // Admin toggles status to inactive
        $this->actingAs($admin)->patch(route('admin.document-types.toggle-status', $docType->id));

        // Resident checks: no longer in selection
        $resResponse2 = $this->actingAs($resident)->get(route('resident.request.create'));
        $resResponse2->assertDontSee($uniqueDocName);

        // Admin permanently deletes another unused doc type
        $anotherDoc = DocumentType::create([
            'name'      => 'Delete Me Doc ' . uniqid(),
            'fee'       => 0.00,
            'is_active' => true,
        ]);
        $this->actingAs($admin)->delete(route('admin.document-types.destroy', $anotherDoc->id));

        $resResponse3 = $this->actingAs($resident)->get(route('resident.request.create'));
        $resResponse3->assertDontSee($anotherDoc->name);
    }

    public function test_resident_can_request_dynamically_added_document_with_auto_assigned_fee(): void
    {
        $admin = $this->createAdmin();
        $resident = $this->createResident();
        $uniqueDocName = 'Senior Citizen Subsidy Card ' . uniqid();
        $this->createdDocTypeNames[] = $uniqueDocName;

        DocumentType::create([
            'name'            => $uniqueDocName,
            'fee'             => 85.00,
            'processing_days' => '2 Days',
            'requirements'    => ['Valid Government ID', 'Senior Citizen ID'],
            'is_active'       => true,
        ]);

        // Resident submits request
        $response = $this->actingAs($resident)->post(route('resident.request.store'), [
            'first_name'     => 'Juan',
            'last_name'      => 'Dela Cruz',
            'contact_number' => '09123456789',
            'document_type'  => $uniqueDocName,
            'purpose'        => 'SUBSIDY APPLICATION 2026',
        ]);

        $response->assertRedirect(route('resident.dashboard'));

        $created = DocumentRequest::where('document_type', $uniqueDocName)->latest()->first();
        $this->assertNotNull($created);
        $this->assertEquals($uniqueDocName, $created->document_type);
        $this->assertEquals(85.00, (float)$created->fee);
        $this->assertEquals('SUBSIDY APPLICATION 2026', $created->purpose);
    }

    public function test_welcome_page_dynamically_renders_active_document_types(): void
    {
        $uniqueDocName = 'Barangay Certification Test ' . uniqid();
        $this->createdDocTypeNames[] = $uniqueDocName;

        DocumentType::create([
            'name'            => $uniqueDocName,
            'fee'             => 120.00,
            'processing_days' => '24 Hours',
            'description'     => 'Certification for test purpose',
            'requirements'    => ['Valid Government ID'],
            'is_active'       => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee($uniqueDocName);
        $response->assertSee('₱120.00');
    }
}
