<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminRequestsUiTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);

        $this->admin = User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Admin Officer Requests',
            'first_name'        => 'Admin',
            'last_name'         => 'Officer',
            'email'             => 'admin_req_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);
    }

    public function test_admin_can_view_requests_with_leading_arrows_pipeline(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.requests'));

        $response->assertStatus(200);

        // Header
        $response->assertSee('Request Management');
        $response->assertSee('Manage and process document requests from residents');

        // High-Contrast Stat Cards
        $response->assertSee('Total Requests');
        $response->assertSee('Pending Reviews');
        $response->assertSee('Approved Today');
        $response->assertSee('Overdue Requests');
        $response->assertSee('Avg Turnaround');

        // Process Pipeline with Sequential Steps & Connectors
        $response->assertSee('Pending &amp; Review', false);
        $response->assertSee('Processing');
        $response->assertSee('Ready for Pick-up');
        $response->assertSee('Completed');
        $response->assertSee('Rejected');
        $response->assertSee('pipeline-arrow-connector');
        $response->assertSee('step-badge-number');
    }

    public function test_admin_can_filter_by_pipeline_stages(): void
    {
        // Processing stage
        $procResponse = $this->actingAs($this->admin)->get(route('admin.requests', ['status' => 'Processing']));
        $procResponse->assertStatus(200);

        // Ready for Pick-up stage
        $readyResponse = $this->actingAs($this->admin)->get(route('admin.requests', ['status' => 'Ready']));
        $readyResponse->assertStatus(200);

        // Completed stage
        $compResponse = $this->actingAs($this->admin)->get(route('admin.requests', ['status' => 'Completed']));
        $compResponse->assertStatus(200);

        // Rejected stage
        $rejResponse = $this->actingAs($this->admin)->get(route('admin.requests', ['status' => 'Rejected']));
        $rejResponse->assertStatus(200);
    }

    public function test_admin_requests_view_contains_quick_filter_chips(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.requests'));

        $response->assertStatus(200);
        $response->assertSee('clickable-kpi-card');
        $response->assertSee('quick_filter');
    }

    public function test_admin_layout_contains_responsive_sidebar_toggle(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="topbarSidebarToggle"', false);
        $response->assertSee('topbar-sidebar-toggle-btn d-lg-none', false);
        $response->assertSee('sidebarBackdrop');
        $response->assertSee('sidebar-wrapper');
    }

    public function test_admin_requests_view_contains_rejection_confirmation_modal(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.requests'));

        $response->assertStatus(200);
        $response->assertSee('id="rejectRequestConfirmModal"', false);
        $response->assertSee('openRejectRequestModal', false);
        $response->assertDontSee("confirm('Reject this request? The resident will be notified.')", false);
    }

    public function test_admin_can_reject_request_and_it_is_deleted(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . rand(10000, 99999),
            'name' => 'Reject Test Resident',
            'email' => 'reject_' . uniqid() . '@example.com',
            'role' => 'Resident',
            'status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $docRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-REJ-' . strtoupper(substr(uniqid(), -6)),
            'first_name' => 'Reject',
            'last_name' => 'Resident',
            'resident_email' => $resident->email,
            'document_type' => 'Barangay Clearance',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.requests.reject', $docRequest->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('document_requests', [
            'id' => $docRequest->id,
        ]);
    }

    public function test_processing_request_allows_word_doc_generation_and_ready_request_shows_attached_file(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . rand(10000, 99999),
            'name' => 'Word Workflow Resident',
            'email' => 'word_workflow_' . uniqid() . '@example.com',
            'role' => 'Resident',
            'status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $processingRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-PROC-' . strtoupper(substr(uniqid(), -6)),
            'first_name' => 'Proc',
            'last_name' => 'Resident',
            'resident_email' => $resident->email,
            'document_type' => 'Certificate of Residency',
            'status' => 'Processing',
        ]);

        $readyRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-READY-' . strtoupper(substr(uniqid(), -6)),
            'first_name' => 'Ready',
            'last_name' => 'Resident',
            'resident_email' => $resident->email,
            'document_type' => 'Barangay Clearance',
            'status' => 'Ready',
        ]);

        // 1. In Processing: Generate Word Doc is active and not d-none for this request, placed at bottom of card, big and visible
        $procResponse = $this->actingAs($this->admin)->get(route('admin.requests', ['status' => 'Processing']));
        $procResponse->assertStatus(200);
        $procResponse->assertSee('modal-generate-doc-btn-' . $processingRequest->id);
        $procResponse->assertDontSee('modal-generate-doc-btn-' . $processingRequest->id . ' d-none', false);
        $procResponse->assertSee('w-100 rounded-3 py-2.5 px-4 fw-bold', false);
        // And attached card is d-none for processing
        $procResponse->assertSee('modal-attached-word-card-' . $processingRequest->id . ' d-none', false);

        // 2. In Ready: Generate Word Doc is hidden (d-none) and cannot be generated anymore
        $readyResponse = $this->actingAs($this->admin)->get(route('admin.requests', ['status' => 'Ready']));
        $readyResponse->assertStatus(200);
        $readyResponse->assertSee('modal-generate-doc-btn-' . $readyRequest->id . ' d-none', false);
        // And attached card and attached doc button are active (not d-none)
        $readyResponse->assertDontSee('modal-attached-word-card-' . $readyRequest->id . ' d-none', false);
        $readyResponse->assertDontSee('modal-attached-doc-btn-' . $readyRequest->id . ' d-none', false);
        $readyResponse->assertSee('Attached Word File');
        $readyResponse->assertSee('Official Document');
    }

    public function test_admin_can_search_requests_by_tracking_number_without_query_exception(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . rand(10000, 99999),
            'name' => 'Search Test Resident',
            'email' => 'search_test_' . uniqid() . '@example.com',
            'role' => 'Resident',
            'status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $trackingNumber = 'REQ-TEST-' . uniqid();
        $docRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => $trackingNumber,
            'first_name' => 'Search',
            'last_name' => 'Resident',
            'resident_email' => $resident->email,
            'document_type' => 'Barangay Clearance',
            'status' => 'Processing',
        ]);

        // Search specifically with the exact tracking number from user query
        $response = $this->actingAs($this->admin)->get(route('admin.requests', [
            'search' => $trackingNumber,
        ]));

        $response->assertStatus(200);
        $response->assertSee($trackingNumber);
        $response->assertSee('Search Results');
    }

    public function test_admin_can_search_requests_by_resident_name(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . rand(10000, 99999),
            'name' => 'Clara Search Resident',
            'email' => 'clara_' . uniqid() . '@example.com',
            'role' => 'Resident',
            'status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $trackingNumber = 'REQ-NAME-' . uniqid();
        $docRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => $trackingNumber,
            'first_name' => 'Maria',
            'last_name' => 'Clara',
            'resident_email' => $resident->email,
            'document_type' => 'Certificate of Residency',
            'status' => 'Pending',
        ]);

        // Search by first name, last name, and full name
        $response = $this->actingAs($this->admin)->get(route('admin.requests', [
            'search' => 'Maria Clara',
        ]));

        $response->assertStatus(200);
        $response->assertSee($trackingNumber);
    }
}
