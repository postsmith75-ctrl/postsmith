<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsmithGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_postsmith_brand(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('PostSmith')
            ->assertSee('#4f46e5')
            ->assertSee('Turn rough thoughts into posts people reply to.');
    }

    public function test_guest_can_generate_with_fallback_engine(): void
    {
        $this->followRedirects($this->post('/generate', [
            'thought' => 'I learned that consistency matters more than perfect timing',
            'platform' => 'LinkedIn',
            'length' => 'medium',
            'drivers' => ['Real Talk', 'Story'],
        ]))
            ->assertOk()
            ->assertSee('Real Talk')
            ->assertSee('Story');
    }
}
