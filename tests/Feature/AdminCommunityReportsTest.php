<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CommunityConcern;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminCommunityReportsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $resident;
    private CommunityConcern $concern;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);

        $this->admin = User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Admin Officer Concerns',
            'first_name'        => 'Admin',
            'last_name'         => 'Officer',
            'email'             => 'admin_concerns_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);

        $this->resident = User::create([
            'user_id'           => 'RES-' . rand(10000, 99999),
            'name'              => 'Troy Catarata Suson',
            'first_name'        => 'Troy',
            'last_name'         => 'Suson',
            'email'             => 'troy_concern_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'),
            'role'              => 'Resident',
            'status'            => 'active',
        ]);

        $this->concern = CommunityConcern::create([
            'concern_id'     => 'CON-' . date('Y') . '-' . rand(10000, 99999),
            'user_id'        => $this->resident->id,
            'resident_name'  => $this->resident->name,
            'resident_email' => $this->resident->email,
            'contact_number' => '09123456789',
            'title'          => 'Damaged Drainage Cover in Sitio Sambag',
            'category'       => 'Infrastructure & Roads',
            'location'       => 'Sitio Sambag',
            'description'    => 'The concrete drainage cover along the main road is cracked and poses safety hazards.',
            'status'         => 'Under Review',
        ]);
    }

    public function test_admin_can_view_community_reports_with_clickable_selections(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.concerns'));

        $response->assertStatus(200);

        // Header & Page Titles
        $response->assertSee('Community Reports');
        $response->assertSee('Reports Management');
        $response->assertSee('Review, update, and respond to resident-submitted community reports and concerns.');

        // Concern details are rendered
        $response->assertSee($this->concern->concern_id);
        $response->assertSee($this->concern->resident_name);
        $response->assertSee('Damaged Drainage Cover in Sitio Sambag');
        $response->assertSee('Infrastructure &amp; Roads', false);
        $response->assertSee('Sitio Sambag');
        $response->assertSee('Under Review');

        // Selection is clickable without the view button
        $response->assertSee('concern-row-clickable');
        $response->assertSee('data-bs-target="#manageConcernModal' . $this->concern->id . '"', false);
        $response->assertDontSee('View / Update');
        $response->assertDontSee('View & Update Concern');

        // Details modal is present
        $response->assertSee('id="manageConcernModal' . $this->concern->id . '"', false);
        $response->assertSee('Detailed Description');
        $response->assertSee($this->concern->description);
    }

    public function test_admin_can_update_community_concern_status_and_remarks(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.concerns.update', $this->concern->id), [
            'status'        => 'In Progress',
            'admin_remarks' => 'Barangay maintenance crew dispatched to replace the concrete cover.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('community_concerns', [
            'id'            => $this->concern->id,
            'status'        => 'In Progress',
            'admin_remarks' => 'Barangay maintenance crew dispatched to replace the concrete cover.',
        ]);
    }
}
