<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentRequest;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminBulkDeleteAndProfileTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);

        $this->admin = User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Admin Officer',
            'first_name'        => 'Admin',
            'last_name'         => 'Officer',
            'email'             => 'admin_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('Password123!'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);
    }

    public function test_logged_in_resident_sees_dashboard_button_instead_of_create_account_on_home()
    {
        $resident = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Resident User',
            'first_name'        => 'Resident',
            'last_name'         => 'User',
            'email'             => 'resident_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('Password123!'),
            'role'              => 'Resident',
            'status'            => 'active',
        ]);

        $response = $this->actingAs($resident)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('My Dashboard');
        $response->assertDontSee('id="heroSignupBtn"', false);
    }

    public function test_admin_can_update_profile_picture_and_remove_it()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($this->admin)->post(route('admin.profile.update'), [
            'name'            => 'Admin Super',
            'email'           => $this->admin->email,
            'contact_number'  => '09123456789',
            'profile_picture' => $file,
        ]);

        $response->assertRedirect();
        $this->admin->refresh();
        $this->assertNotNull($this->admin->profile_picture);
        Storage::disk('public')->assertExists($this->admin->profile_picture);

        // Test removing profile picture
        $removeResponse = $this->actingAs($this->admin)->post(route('admin.profile.update'), [
            'name'                   => 'Admin Super',
            'email'                  => $this->admin->email,
            'contact_number'         => '09123456789',
            'remove_profile_picture' => '1',
        ]);

        $removeResponse->assertRedirect();
        $this->admin->refresh();
        $this->assertNull($this->admin->profile_picture);
    }

    public function test_admin_can_bulk_delete_document_requests()
    {
        $req1 = DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'resident_name'   => 'Resident One',
            'first_name'      => 'Resident',
            'last_name'       => 'One',
            'document_type'   => 'Barangay Clearance',
            'status'          => 'Pending',
        ]);

        $req2 = DocumentRequest::create([
            'tracking_number' => 'REQ-TEST-' . uniqid(),
            'resident_name'   => 'Resident Two',
            'first_name'      => 'Resident',
            'last_name'       => 'Two',
            'document_type'   => 'Certificate of Residency',
            'status'          => 'Pending',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.requests.bulk-delete'), [
            'ids' => [$req1->id, $req2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('document_requests', ['id' => $req1->id]);
        $this->assertDatabaseMissing('document_requests', ['id' => $req2->id]);
    }

    public function test_admin_can_bulk_delete_users_excluding_themselves()
    {
        $user1 = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Resident A',
            'first_name'        => 'Resident',
            'last_name'         => 'A',
            'email'             => 'user1_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('Password123!'),
            'role'              => 'Resident',
            'status'            => 'active',
        ]);

        $user2 = User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Resident B',
            'first_name'        => 'Resident',
            'last_name'         => 'B',
            'email'             => 'user2_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('Password123!'),
            'role'              => 'Resident',
            'status'            => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.bulk-delete'), [
            'ids' => [$user1->id, $user2->id, $this->admin->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
        // Admin itself should NOT be deleted
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_bulk_delete_activity_logs()
    {
        $log1 = ActivityLog::create([
            'user_id'     => $this->admin->id,
            'action'      => 'test_action_1',
            'description' => 'Test log 1',
            'ip_address'  => '127.0.0.1',
        ]);

        $log2 = ActivityLog::create([
            'user_id'     => $this->admin->id,
            'action'      => 'test_action_2',
            'description' => 'Test log 2',
            'ip_address'  => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.activity-logs.bulk-delete'), [
            'ids' => [$log1->id, $log2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('activity_logs', ['id' => $log1->id]);
        $this->assertDatabaseMissing('activity_logs', ['id' => $log2->id]);
    }

    public function test_admin_can_view_activity_logs_without_ip_address_column()
    {
        ActivityLog::create([
            'user_id'     => $this->admin->id,
            'action'      => 'test_action',
            'description' => 'Test log description',
            'ip_address'  => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs'));

        $response->assertStatus(200);
        $response->assertSee('Log Entries');
        $response->assertSee('DESCRIPTION');
        $response->assertDontSee('IP ADDRESS');
    }
}
