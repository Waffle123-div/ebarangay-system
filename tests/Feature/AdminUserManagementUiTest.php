<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminUserManagementUiTest extends TestCase
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
            'name'              => 'Admin Test User',
            'first_name'        => 'Admin',
            'last_name'         => 'Test',
            'email'             => 'admin_test_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);
    }

    public function test_admin_can_view_user_management_with_refactored_header_and_cards(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users'));

        $response->assertStatus(200);

        // Standardized title & breadcrumb
        $response->assertSee('User Management');
        $response->assertSee('Dashboard');
        $response->assertSee('Manage resident accounts, permissions, and system roles');
        $response->assertSee('+ Create New User');

        // Merged 4 Metric / Filter cards
        $response->assertSee('All Users');
        $response->assertSee('Residents');
        $response->assertSee('Administrators');
        $response->assertSee('Pending / New');

        // Table Control Bar
        $response->assertSee('Search by name, ID, or email...');
        $response->assertSee('Filters');
    }

    public function test_admin_can_filter_by_card_tabs(): void
    {
        // Resident tab
        $resResponse = $this->actingAs($this->admin)->get(route('admin.users', ['role' => 'Resident']));
        $resResponse->assertStatus(200);

        // Admin tab
        $admResponse = $this->actingAs($this->admin)->get(route('admin.users', ['role' => 'Admin']));
        $admResponse->assertStatus(200);

        // Pending / New tab
        $penResponse = $this->actingAs($this->admin)->get(route('admin.users', ['role' => 'Pending']));
        $penResponse->assertStatus(200);
    }

    public function test_admin_can_create_new_user_via_store_route(): void
    {
        $email = 'new_resident_' . uniqid() . '@example.com';

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'     => 'JUAN PEDRO TEST',
            'email'    => $email,
            'role'     => 'Resident',
            'status'   => 'active',
            'password' => 'SecurePass123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name'  => 'JUAN PEDRO TEST',
            'role'  => 'Resident',
        ]);
    }
}
