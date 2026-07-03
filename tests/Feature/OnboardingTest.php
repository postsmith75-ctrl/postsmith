<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_user_is_redirected_to_onboarding_from_dashboard(): void
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('onboarding.show'));
    }

    public function test_completed_user_continues_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back');
    }

    public function test_onboarding_saves_first_ai_memory_and_marks_user_complete(): void
    {
        $user = User::factory()->create([
            'onboarding_completed_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('onboarding.store'), [
                'use_case' => 'Business',
                'brand_context' => "I own a fashion business that sells affordable women's clothing.",
            ])
            ->assertRedirect(route('onboarding.show'));

        $user->refresh();

        $this->assertNotNull($user->onboarding_completed_at);
        $this->assertDatabaseHas('user_memories', [
            'user_id' => $user->id,
            'type' => 'profile',
            'source' => 'onboarding',
            'content' => "I own a fashion business that sells affordable women's clothing.",
            'active' => true,
        ]);
    }

    public function test_ai_generation_includes_user_memory_in_prompt_context(): void
    {
        config([
            'postsmith.ai.provider' => 'openai',
            'postsmith.ai.api_key' => 'test-key',
        ]);

        Http::fake([
            'https://api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'posts' => [[
                                'driver' => 'Story',
                                'text' => 'A fashion-focused post.',
                                'why_it_works' => 'It uses remembered brand context.',
                            ]],
                        ]),
                    ],
                ]],
            ]),
        ]);

        $user = User::factory()->create();
        $user->aiMemories()->create([
            'type' => 'profile',
            'source' => 'onboarding',
            'title' => 'Profile',
            'content' => "I own a fashion business that sells affordable women's clothing.",
            'metadata' => ['use_case' => 'Business'],
            'active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('generate'), [
                'thought' => 'announce new arrivals',
                'platform' => 'Instagram',
                'length' => 'short',
                'drivers' => ['Story'],
            ])
            ->assertRedirect(route('dashboard'));

        Http::assertSent(function ($request) {
            $body = $request->data();
            $prompt = data_get($body, 'messages.1.content', '');

            return str_contains($prompt, 'User AI Memory')
                && str_contains($prompt, "affordable women's clothing")
                && str_contains($prompt, 'announce new arrivals');
        });
    }
}
