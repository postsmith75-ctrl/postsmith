<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_with_name_email_and_password(): void
    {
        Mail::fake();

        $this->post(route('register.store'), [
            'name' => 'Ada Creator',
            'email' => 'Ada@Example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'ada@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Ada Creator', $user->name);
        $this->assertFalse($user->email_verified);
        $this->assertNotNull($user->verification_code);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_guest_can_login_with_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'creator@example.com',
            'password' => 'password123',
        ]);

        $this->post(route('login.store'), [
            'email' => 'creator@example.com',
            'password' => 'password123',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_auth_pages_include_google_option(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Sign up with Google')
            ->assertSee(route('auth.google.redirect'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in with Google')
            ->assertSee(route('auth.google.redirect'));
    }
}
