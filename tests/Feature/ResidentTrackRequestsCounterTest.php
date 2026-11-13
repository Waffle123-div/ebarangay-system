<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ResidentTrackRequestsCounterTest extends TestCase
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

    public function test_sidebar_shows_active_requests_counter_badge(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Badge Resident Test',
            'first_name' => 'Badge',
            'last_name' => 'Resident',
            'email' => 'resident_badge_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        // Create 2 active requests and 1 completed request
        DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'user_id' => $resident->id,
            'first_name' => 'Badge',
            'last_name' => 'Resident',
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Employment',
            'status' => 'Pending',
        ]);

        DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'user_id' => $resident->id,
            'first_name' => 'Badge',
            'last_name' => 'Resident',
            'document_type' => 'Certificate of Indigency',
            'purpose' => 'Scholarship',
            'status' => 'Processing',
        ]);

        DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'user_id' => $resident->id,
            'first_name' => 'Badge',
            'last_name' => 'Resident',
            'document_type' => 'Certificate of Residency',
            'purpose' => 'Bank Account',
            'status' => 'Completed',
        ]);

        $response = $this->actingAs($resident)->get(route('resident.request.track'));
        $response->assertStatus(200);

        // Assert the badge exists and shows '2'
        $response->assertSee('id="rTrackRequestsBadge"', false);
        $response->assertSee('>2</span>', false);

        // Assert checkUpdates API returns active_count = 2
        $apiResponse = $this->actingAs($resident)->get(route('resident.requests.check'));
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment(['active_count' => 2]);
    }

    public function test_sidebar_badge_is_hidden_when_no_active_requests(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Zero Badge Resident',
            'first_name' => 'Zero',
            'last_name' => 'Resident',
            'email' => 'zero_badge_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        // Only completed or rejected requests
        DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'user_id' => $resident->id,
            'first_name' => 'Zero',
            'last_name' => 'Resident',
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Employment',
            'status' => 'Completed',
        ]);

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));
        $response->assertStatus(200);

        // Assert badge has d-none class
        $response->assertSee('r-sidebar-badge d-none', false);

        // Assert checkUpdates returns active_count = 0
        $apiResponse = $this->actingAs($resident)->get(route('resident.requests.check'));
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment(['active_count' => 0]);
    }

    public function test_resident_portal_sections_render_user_scoped_persistent_istong_chat(): void
    {
        $resident = User::create([
            'user_id' => 'RES-' . substr(uniqid(), -6) . rand(10, 99),
            'name' => 'Chat Persist Resident',
            'first_name' => 'Chat',
            'last_name' => 'Resident',
            'email' => 'chat_persist_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin' => bcrypt('1234'),
            'password' => bcrypt('Password123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $expectedKeyFragment = "'ebarangay_resident_istong_v1_' + chatUserId";

        foreach ([
            route('resident.dashboard'),
            route('resident.request.create'),
            route('resident.request.track'),
            route('resident.concerns'),
            route('resident.activity-logs'),
            route('resident.settings'),
        ] as $sectionUrl) {
            $response = $this->actingAs($resident)->get($sectionUrl);
            $response->assertStatus(200);
            $response->assertSee('id="reset-chat-btn"', false);
            $response->assertSee($expectedKeyFragment, false);
            $response->assertSee('const chatUserId = "' . $resident->id . '"', false);
            $response->assertSee('restoreStoredChat()', false);
        }
    }
}

