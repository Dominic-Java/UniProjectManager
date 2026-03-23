<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guest_can_view_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_guest_theme_cookie_is_respected_on_landing(): void
    {
        $response = $this->withCookie('upm_theme', 'dark')->get('/');

        $response->assertStatus(200);
        $response->assertSee('data-theme="dark"', false);
    }
}
