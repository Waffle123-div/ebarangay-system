<?php

namespace Tests\Feature;

use App\Models\CommunityConcern;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DarkModeVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    private User $resident;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);

        $this->resident = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Dark Mode Resident',
            'first_name'        => 'Dark',
            'last_name'         => 'Resident',
            'email'             => 'dm_resident_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin'      => bcrypt('123456'),
            'password'          => bcrypt('Password123!'),
            'role'              => 'resident',
            'status'            => 'active',
        ]);

        $this->admin = User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Dark Mode Admin',
            'first_name'        => 'Dark',
            'last_name'         => 'Admin',
            'email'             => 'dm_admin_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('Password123!'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);
    }

    public function test_shared_theme_stylesheet_defines_dark_mode_foundations(): void
    {
        $css = file_get_contents(public_path('css/ebarangay-theme.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('color-scheme: dark;', $css);
        $this->assertStringContainsString('.receipt-header-divider', $css);
        $this->assertStringContainsString('html[data-theme="dark"] .btn-outline-primary', $css);
        $this->assertStringContainsString('html[data-theme="dark"] .btn-warning', $css);
        $this->assertStringContainsString('html[data-theme="dark"] .btn-light', $css);
        $this->assertStringContainsString('html[data-theme="dark"] .alert-success', $css);
        $this->assertStringContainsString('html[data-theme="dark"] .alert-primary', $css);
        $this->assertStringContainsString('html[data-theme="dark"] .btn-close', $css);
        $this->assertStringContainsString('html[data-theme="dark"] .list-group-item', $css);
    }

    public function test_resident_portal_views_render_dark_mode_fixes_without_blanket_fw_bold_override(): void
    {
        $readyRequest = DocumentRequest::create([
            'tracking_number' => 'REQ-DM-' . uniqid(),
            'user_id'         => $this->resident->id,
            'first_name'      => 'Dark',
            'last_name'       => 'Resident',
            'document_type'   => 'Barangay Clearance',
            'purpose'         => 'Employment',
            'status'          => 'Ready',
        ]);

        CommunityConcern::create([
            'concern_id'    => 'CON-DM-' . substr(uniqid(), -5),
            'user_id'       => $this->resident->id,
            'resident_name' => 'Dark Mode Resident',
            'title'         => 'Streetlight Outage',
            'category'      => 'Utilities',
            'description'   => 'Broken streetlight near corner.',
            'location'      => 'Purok 1',
            'status'        => 'Pending',
        ]);

        // 1. Dashboard & Receipt modal divider + announcement dark mode
        $dashboard = $this->actingAs($this->resident)->get(route('resident.dashboard'));
        $dashboard->assertOk();
        $dashHtml = $dashboard->getContent();
        $this->assertStringContainsString('html[data-theme="dark"] .text-primary', $dashHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .session-lock-icon-circle', $dashHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .announcement-type-info', $dashHtml);
        $this->assertStringContainsString('receipt-header-divider', $dashHtml);
        $this->assertStringNotContainsString("html[data-theme=\"dark\"] .fw-bold, html[data-bs-theme=\"dark\"] .fw-bold,", $dashHtml);

        // 2. Request Document
        $requestDoc = $this->actingAs($this->resident)->get(route('resident.request.create'));
        $requestDoc->assertOk();
        $reqDocHtml = $requestDoc->getContent();
        $this->assertStringContainsString('html[data-theme="dark"] .file-type-select', $reqDocHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .stepper-step.active .stepper-circle', $reqDocHtml);
        $this->assertStringNotContainsString('background-color:#f0f7ff;', $reqDocHtml);
        $this->assertStringNotContainsString('${icon}${ext === \'pdf\'', $reqDocHtml);

        // 3. Track Request
        $track = $this->actingAs($this->resident)->get(route('resident.request.track'));
        $track->assertOk();
        $trackHtml = $track->getContent();
        $this->assertStringContainsString('html[data-theme="dark"] .claim-stub-card', $trackHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .accordion-button::after', $trackHtml);
        $this->assertStringContainsString("document.documentElement.getAttribute('data-theme') === 'dark' ? '#131d31' : '#ffffff'", $trackHtml);

        // 4. Receipt view
        $receipt = $this->actingAs($this->resident)->get(route('resident.receipt.view', $readyRequest->id));
        $receipt->assertOk();
        $receiptHtml = $receipt->getContent();
        $this->assertStringContainsString('receipt-header-divider', $receiptHtml);

        // 5. Community Concerns & Activity Logs
        $this->actingAs($this->resident)->get(route('resident.concerns'))->assertOk();
        $this->actingAs($this->resident)->get(route('resident.activity-logs'))->assertOk();

        // 6. Settings
        $settings = $this->actingAs($this->resident)->get(route('resident.settings'));
        $settings->assertOk();
        $settingsHtml = $settings->getContent();
        $this->assertStringContainsString('html[data-theme="dark"] .settings-card', $settingsHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .form-control-settings', $settingsHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .otp-channel-card', $settingsHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .settings-alert-warning', $settingsHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .settings-alert-danger', $settingsHtml);
    }

    public function test_admin_portal_views_render_dark_mode_fixes_without_blanket_fw_bold_override(): void
    {
        DocumentType::firstOrCreate(
            ['name' => 'Dark Mode Test Cert'],
            ['fee' => 0, 'processing_days' => '1 Day', 'is_active' => true]
        );

        DocumentRequest::create([
            'tracking_number' => 'REQ-ADM-' . uniqid(),
            'user_id'         => $this->resident->id,
            'first_name'      => 'Dark',
            'last_name'       => 'Resident',
            'document_type'   => 'Barangay Clearance',
            'purpose'         => 'Employment',
            'status'          => 'Ready',
        ]);

        CommunityConcern::create([
            'concern_id'    => 'CON-ADM-' . substr(uniqid(), -5),
            'user_id'       => $this->resident->id,
            'resident_name' => 'Dark Mode Resident',
            'title'         => 'Drainage Issue',
            'category'      => 'Sanitation',
            'description'   => 'Clogged canal.',
            'location'      => 'Purok 2',
            'status'        => 'Pending',
        ]);

        // Dashboard
        $dashboard = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashboard->assertOk();
        $dashHtml = $dashboard->getContent();
        $this->assertStringContainsString('html[data-theme="dark"] .mobile-log-card', $dashHtml);
        $this->assertStringContainsString('html[data-theme="dark"] .btn-close', $dashHtml);
        $this->assertStringNotContainsString("html[data-theme=\"dark\"] .fw-bold, html[data-bs-theme=\"dark\"] .fw-bold,", $dashHtml);

        // Requests
        $requests = $this->actingAs($this->admin)->get(route('admin.requests'));
        $requests->assertOk();
        $this->assertStringContainsString('attached-word-card', $requests->getContent());

        // Concerns
        $concerns = $this->actingAs($this->admin)->get(route('admin.concerns'));
        $concerns->assertOk();
        $this->assertStringContainsString('concern-id-badge', $concerns->getContent());

        // Document Management
        $docTypes = $this->actingAs($this->admin)->get(route('admin.document-types.index'));
        $docTypes->assertOk();
        $this->assertStringContainsString('doc-type-row-icon', $docTypes->getContent());

        // Users, Activity Logs, Profile
        $users = $this->actingAs($this->admin)->get(route('admin.users'));
        $users->assertOk();
        $this->assertStringContainsString('html[data-theme="dark"] .text-slate-800', $users->getContent());

        $this->actingAs($this->admin)->get(route('admin.activity-logs'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.profile'))->assertOk();
    }
}
