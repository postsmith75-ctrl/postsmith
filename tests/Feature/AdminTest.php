<?php

namespace Tests\Feature;

use App\Models\DiscoveredDriver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_local_dev_admin_credentials_can_view_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'PostSmith Admin',
            'email' => 'admin@postsmith.local',
            'password' => 'Admin@12345',
            'role' => 'admin',
            'tier' => 'pro',
        ]);

        $this->assertTrue(Hash::check('Admin@12345', $admin->password));

        $this->post(route('login.store'), [
            'email' => 'admin@postsmith.local',
            'password' => 'Admin@12345',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('PostSmith Admin');
    }
}
