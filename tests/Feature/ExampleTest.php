<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExampleTest extends TestCase
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

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('Admin Dashboard');
        $response->assertSee('Sign Up');
        $response->assertSee('Log In');
    }

    public function test_landing_page_does_not_show_admin_dashboard_for_admin_user(): void
    {
        $admin = new User([
            'name' => 'Admin Test User',
            'email' => 'admin_landing_test@example.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/home');

        $response->assertStatus(200);
        $response->assertDontSee('Admin Dashboard');
        $response->assertSee('Sign Up');
        $response->assertSee('Log In');
    }

    public function test_landing_page_shows_resident_dashboard_for_logged_in_resident(): void
    {
        $resident = new User([
            'name' => 'Resident Test User',
            'email' => 'resident_landing_test@example.com',
            'role' => 'resident',
        ]);

        $response = $this->actingAs($resident)->get('/home');

        $response->assertStatus(200);
        $response->assertDontSee('Admin Dashboard');
        $response->assertSee('My Resident Dashboard');
    }

    public function test_landing_page_renders_istong_ai_chat_widget(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Istong AI');
        $response->assertSee('chatWidget');
        $response->assertSee('chatInput');
        $response->assertSee('chatForm');
        $response->assertSee('chatResetBtn');
        $response->assertDontSee('E-Barangay Assistant');
    }

    public function test_chatbot_endpoint_returns_reply(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.groq.com/*' => \Illuminate\Support\Facades\Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Maayong adlaw! Ako si Istong, unsaon nako pagtabang nimo?',
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('chatbot.message'), [
            'history' => [
                ['role' => 'user', 'content' => 'Kumusta Istong?']
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'Maayong adlaw! Ako si Istong, unsaon nako pagtabang nimo?'
        ]);
    }

    public function test_landing_page_does_not_contain_news_and_community_concerns_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('Barangay News & Announcements');
        $response->assertDontSee('Submit a Community Concern');
        $response->assertDontSee('E-Barangay System Now Live!');
        $response->assertDontSee('Free Barangay Clearance Day');
        $response->assertDontSee('id="news"', false);
        $response->assertDontSee('id="concerns"', false);
        $response->assertDontSee('href="#news"', false);
        $response->assertDontSee('href="#concerns"', false);
    }

    public function test_landing_page_includes_cache_busted_assets_and_stylesheet_fallback(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('css/style.css?v=', false);
        $response->assertSee('js/app.js?v=', false);
        $response->assertSee('mainStylesheet', false);
    }
}

