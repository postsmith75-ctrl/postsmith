<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_flutterwave_verification_upgrades_user_to_starter(): void
    {
        config(['postsmith.payments.flutterwave_secret_key' => 'flw-secret']);

        Http::fake([
            'api.flutterwave.com/v3/transactions/12345/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'successful',
                    'amount' => 4.50,
                    'currency' => 'USD',
                ],
            ]),
        ]);

        $user = User::factory()->create([
            'tier' => 'free',
            'generations_used' => 8,
        ]);

        $this->actingAs($user)
            ->get(route('billing.flutterwave.verify', [
                'transaction_id' => '12345',
                'tx_ref' => 'POSTSMITH_'.$user->id.'_starter_monthly_1',
                'tier' => 'starter',
                'plan' => 'monthly',
            ]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertSame('starter', $user->tier);
        $this->assertSame(0, $user->generations_used);
        $this->assertNotNull($user->pro_expires_at);
    }
}
