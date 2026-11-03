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
        $response->assertSee('Pending & Review', false);
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
}
