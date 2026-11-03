<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminAuthTest extends TestCase
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

    public function test_admin_login_page_renders_successfully(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Admin Authentication');
        $response->assertSee('adminEmail');
        $response->assertSee('adminPassword');
        $response->assertSee('Authorize &amp; Enter Dashboard', false);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-' . substr(uniqid(), -6),
            'name' => 'Admin Controller Test',
            'email' => 'adm_auth_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('AdminSecret123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'AdminSecret123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => 'wrong_admin@example.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}

