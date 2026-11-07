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

    public function test_user_table_and_mobile_cards_are_clickable_without_view_and_edit_buttons(): void
    {
        $resident = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'CLICKABLE ROW USER',
            'email'             => 'clickable_' . uniqid() . '@example.com',
            'role'              => 'Resident',
            'status'            => 'active',
            'password'          => bcrypt('password123'),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users'));
        $response->assertStatus(200);

        // View and Edit buttons removed from table and cards
        $response->assertDontSee('<span>View</span>', false);
        $response->assertDontSee('<span>Edit</span>', false);
        $response->assertDontSee('> View</button>', false);
        $response->assertDontSee('> Edit</button>', false);

        // Clickable rows enabled
        $response->assertSee('user-row-clickable');

        // Edit button is placed on User Details card
        $response->assertSee('id="viewModalEditBtn"', false);

        // Horizontally expanded view and edit sections exist
        $response->assertSee('id="userModalViewSection"', false);
        $response->assertSee('id="userModalEditSection"', false);
        $response->assertSee('modal-xl');
    }

    public function test_admin_can_update_user_with_expanded_details(): void
    {
        $resident = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'INITIAL RESIDENT NAME',
            'email'             => 'update_test_' . uniqid() . '@example.com',
            'role'              => 'Resident',
            'status'            => 'active',
            'password'          => bcrypt('password123'),
        ]);

        $updatedEmail = 'updated_' . uniqid() . '@example.com';
        $uniquePhone = '09' . mt_rand(100000000, 999999999);

        $response = $this->actingAs($this->admin)->patch(route('admin.users.update', $resident->id), [
            'name'             => 'UPDATED RESIDENT FULLNAME',
            'email'            => $updatedEmail,
            'role'             => 'Resident',
            'phone_number'     => $uniquePhone,
            'gender'           => 'Male',
            'birthdate'        => '1995-05-15',
            'civil_status'     => 'Single',
            'occupation'       => 'Software Engineer',
            'nationality'      => 'Filipino',
            'purok'            => 'Purok Mangga',
            'sitio'            => 'Sitio Central',
            'barangay_address' => 'Cogon East',
            'municipality'     => 'Carmen',
            'province'         => 'Cebu',
            'zip_code'         => '6005',
            'status'           => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'           => $resident->id,
            'name'         => 'UPDATED RESIDENT FULLNAME',
            'email'        => $updatedEmail,
            'phone_number' => $uniquePhone,
            'civil_status' => 'Single',
            'occupation'   => 'Software Engineer',
            'purok'        => 'Purok Mangga',
        ]);
    }
}
