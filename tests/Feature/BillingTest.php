<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_flutterwave_verification_upgrades_user_to_starter(): void
    {
        config(['postsmith.payments.flutterwave_secret_key' => 'flw-secret']);
        Mail::fake();

        Http::fake([
            'api.flutterwave.com/v3/transactions/12345/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 12345,
                    'status' => 'successful',
                    'amount' => 4.50,
                    'currency' => 'USD',
                    'tx_ref' => 'POSTSMITH_TEST_REF',
                    'payment_type' => 'card',
                    'card' => [
                        'type' => 'visa',
                        'last_4digits' => '4242',
                        'expiry' => '12/30',
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create([
            'tier' => 'free',
            'generations_used' => 8,
        ]);

        $this->actingAs($user)
            ->postJson(route('billing.flutterwave.intent'), [
                'tier' => 'starter',
                'plan' => 'monthly',
                'auto_renew' => true,
            ])
            ->assertOk()
            ->assertJsonStructure(['tx_ref', 'amount', 'currency']);

        $payment = $user->payments()->firstOrFail();
        $payment->forceFill(['tx_ref' => 'POSTSMITH_TEST_REF'])->save();

        $this->actingAs($user)
            ->get(route('billing.flutterwave.verify', [
                'transaction_id' => '12345',
                'tx_ref' => 'POSTSMITH_TEST_REF',
            ]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertSame('starter', $user->tier);
        $this->assertSame(0, $user->generations_used);
        $this->assertNotNull($user->pro_expires_at);
        $this->assertTrue($user->billing_auto_renew);
        $this->assertSame('4242', $user->billing_card_last_four);
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'tx_ref' => 'POSTSMITH_TEST_REF',
            'status' => 'paid',
            'amount' => 4.50,
        ]);
    }
}
