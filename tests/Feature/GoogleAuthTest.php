<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_button_points_to_auth_route(): void
    {
        $this->get('/')
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
