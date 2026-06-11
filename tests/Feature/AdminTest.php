<?php

namespace Tests\Feature;

use App\Models\DiscoveredDriver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_view_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_view_dashboard_and_update_driver(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $driver = DiscoveredDriver::create([
            'driver_name' => 'Open Loop',
            'description' => 'Creates curiosity.',
            'psychology' => 'Readers want closure.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('PostSmith Admin');

        $this->actingAs($admin)
            ->patch(route('admin.drivers.update', $driver), ['status' => 'active'])
            ->assertRedirect();

        $this->assertSame('active', $driver->refresh()->status);
        $this->assertNotNull($driver->promoted_at);
    }
}
