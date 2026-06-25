<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_dashboard_links_to_auth_pages_without_google_cta(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertDontSee(route('auth.google.redirect'));
    }

    public function test_login_page_keeps_google_option(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('auth.google.redirect'));
    }

    public function test_google_redirect_fails_cleanly_when_not_configured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('auth');
    }
}
