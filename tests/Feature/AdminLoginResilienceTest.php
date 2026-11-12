<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminLoginResilienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_can_log_in_and_load_admin_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'remember' => 'on',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $dashboard = $this->get(route('admin.dashboard'));
        $dashboard->assertStatus(200);
    }

    public function test_admin_login_recognizes_admin_role_with_whitespace_and_mixed_case(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-TRIM1',
            'name' => 'Trimmed Admin',
            'email' => 'trimmed.admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('SecretPass123!'),
            'role' => ' Admin ',
            'status' => 'Active',
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_returns_friendly_error_instead_of_500_when_database_is_unmigrated(): void
    {
        Schema::dropIfExists('users');

        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'database is unavailable or not migrated yet',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }

    public function test_admin_login_page_still_renders_when_auth_check_throws(): void
    {
        Auth::shouldReceive('check')
            ->once()
            ->andThrow(new \RuntimeException('Simulated DB error'));

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Admin Authentication');
    }
}
