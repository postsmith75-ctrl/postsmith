<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BillingController extends Controller
{
    public function verifyFlutterwave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transaction_id' => ['required', 'string'],
            'tx_ref' => ['required', 'string'],
            'tier' => ['required', 'in:starter,pro'],
            'plan' => ['required', 'in:monthly,annual'],
        ]);

        $secretKey = config('postsmith.payments.flutterwave_secret_key');
        if (! $secretKey) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['payment' => 'Flutterwave secret key is not configured yet.']);
        }

        $verification = Http::withToken($secretKey)
            ->timeout(30)
            ->get("https://api.flutterwave.com/v3/transactions/{$data['transaction_id']}/verify");

        if (! $verification->ok()) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['payment' => 'Payment verification failed.']);
        }

        $payload = $verification->json();
        if (($payload['status'] ?? '') !== 'success' || ($payload['data']['status'] ?? '') !== 'successful') {
            return redirect()
                ->route('dashboard')
                ->withErrors(['payment' => 'Payment was not successful.']);
        }

        $expected = $this->expectedAmount($data['tier'], $data['plan']);
        $paid = (float) ($payload['data']['amount'] ?? 0);

        if ($expected <= 0 || $paid < ($expected * 0.9)) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['payment' => 'Payment amount did not match the selected plan.']);
        }

        $request->user()->forceFill([
            'tier' => $data['tier'],
            'pro_expires_at' => $data['plan'] === 'annual' ? now()->addYear() : now()->addMonth(),
            'downgraded_at' => null,
            'followup_count' => 0,
            'last_followup_sent' => null,
            'generations_used' => 0,
            'viral_reset_at' => now(),
        ])->save();

        return redirect()
            ->route('dashboard')
            ->with('status', ($data['tier'] === 'starter' ? 'Starter' : 'Pro').' is active. Your payment was processed securely via Flutterwave.');
    }

    private function expectedAmount(string $tier, string $plan): float
    {
        return (float) config("postsmith.tiers.{$tier}.checkout_{$plan}_price", 0);
    }
}
