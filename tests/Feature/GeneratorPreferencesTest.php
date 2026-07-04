<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneratorPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_generation_remembers_latest_generator_preferences_separately_from_ai_memory(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('generate'), [
                'thought' => 'Build trust by showing the work behind the result',
                'platform' => 'LinkedIn',
                'goal' => 'Build Brand Awareness',
                'length' => 'medium',
                'drivers' => ['Story'],
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('user_generator_preferences', [
            'user_id' => $user->id,
            'last_platform' => 'LinkedIn',
            'last_goal' => 'Build Brand Awareness',
        ]);

        $this->assertDatabaseMissing('user_memories', [
            'user_id' => $user->id,
            'content' => 'Build Brand Awareness',
        ]);
    }

    public function test_generator_preferences_are_updated_in_place(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('generate'), [
                'thought' => 'First thought',
                'platform' => 'Instagram',
                'goal' => 'Grow Followers',
                'length' => 'short',
                'drivers' => ['Story'],
            ]);

        $this->actingAs($user)
            ->post(route('generate'), [
                'thought' => 'Second thought',
                'platform' => 'X',
                'goal' => 'Generate Leads',
                'length' => 'long',
                'drivers' => ['Real Talk'],
            ]);

        $this->assertSame(1, $user->generatorPreference()->count());
        $this->assertDatabaseHas('user_generator_preferences', [
            'user_id' => $user->id,
            'last_platform' => 'X',
            'last_goal' => 'Generate Leads',
        ]);
    }

    public function test_generation_platform_must_come_from_postsmith_config(): void
    {
        $this->post(route('generate'), [
            'thought' => 'A valid thought',
            'platform' => 'Threads',
            'length' => 'medium',
            'drivers' => ['Story'],
        ])
            ->assertSessionHasErrors('platform');
    }

    public function test_dashboard_displays_content_strategy_defaults_when_no_preferences_exist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('✨ Content Strategy')
            ->assertSee('LinkedIn')
            ->assertSee('Get Engagement')
            ->assertSee('Using your preferred strategy.')
            ->assertSee('Edit')
            ->assertSee('Writing For')
            ->assertSee('Apply');
    }

    public function test_user_can_update_generator_preferences_from_content_strategy(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson(route('generator-preferences.update'), [
                'platform' => 'Facebook',
                'goal' => 'Generate Leads',
            ])
            ->assertOk()
            ->assertJson([
                'platform' => 'Facebook',
                'goal' => 'Generate Leads',
            ]);

        $this->assertDatabaseHas('user_generator_preferences', [
            'user_id' => $user->id,
            'last_platform' => 'Facebook',
            'last_goal' => 'Generate Leads',
        ]);
    }

    public function test_content_strategy_update_rejects_unknown_platform_and_goal_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->patch(route('generator-preferences.update'), [
                'platform' => 'Threads',
                'goal' => 'Go Viral',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors(['platform', 'goal']);

        $this->assertSame(0, $user->generatorPreference()->count());
    }
}
