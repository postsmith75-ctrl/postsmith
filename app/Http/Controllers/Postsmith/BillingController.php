<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function createFlutterwaveIntent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tier' => ['required', 'in:starter,pro'],
            'plan' => ['required', 'in:monthly,annual'],
            'auto_renew' => ['nullable', 'boolean'],
        ]);

        $publicKey = config('postsmith.payments.flutterwave_public_key');
        $amount = $this->expectedAmount($data['tier'], $data['plan']);

        if (! $publicKey || $amount <= 0) {
            return response()->json(['message' => 'Flutterwave checkout is not configured yet.'], 422);
        }

        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'provider' => 'flutterwave',
            'tx_ref' => 'POSTSMITH_'.$request->user()->id.'_'.Str::upper(Str::random(24)),
            'status' => 'pending',
            'purpose' => 'subscription',
            'tier' => $data['tier'],
            'plan' => $data['plan'],
            'amount' => $amount,
            'currency' => config('postsmith.payments.currency'),
            'auto_renew_requested' => $request->boolean('auto_renew'),
        ]);

        return response()->json([
            'public_key' => $publicKey,
            'tx_ref' => $payment->tx_ref,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'payment_plan' => $payment->auto_renew_requested
                ? config("postsmith.payments.flutterwave_payment_plans.{$payment->tier}.{$payment->plan}")
                : null,
        ]);
    }

    public function verifyFlutterwave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transaction_id' => ['required', 'string'],
            'tx_ref' => ['required', 'string'],
        ]);

        $payment = Payment::query()
            ->where('tx_ref', $data['tx_ref'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $payment) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['payment' => 'This payment reference was not created by your account. Please start checkout again.']);
        }

        if ($payment->status === 'paid') {
            return redirect()
                ->route('dashboard')
                ->with('status', 'Your payment is already verified.');
        }

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
            $payment->forceFill([
                'status' => 'failed',
                'raw_payload' => $payload,
            ])->save();

            return redirect()
                ->route('dashboard')
                ->withErrors(['payment' => 'Payment was not successful.']);
        }

        if (($payload['data']['tx_ref'] ?? null) !== $payment->tx_ref) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['payment' => 'Payment reference mismatch. Please contact support.']);
        }

        $expected = $this->expectedAmount($payment->tier, $payment->plan);
        $paid = (float) ($payload['data']['amount'] ?? 0);
        $currency = strtoupper((string) ($payload['data']['currency'] ?? ''));
        $expectedCurrency = strtoupper((string) config('postsmith.payments.currency'));

        if ($expected <= 0 || $paid < ($expected * 0.9) || $currency !== $expectedCurrency) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['payment' => 'Payment amount did not match the selected plan.']);
        }

        $this->activatePayment($payment, $request->user(), $payload['data']);

        return redirect()
            ->route('dashboard')
            ->with('status', ucfirst($payment->tier).' is active. Your payment was processed securely via Flutterwave.');
    }

    public function flutterwaveWebhook(Request $request): JsonResponse
    {
        $secretHash = config('postsmith.payments.flutterwave_webhook_secret_hash');
        if (! $secretHash && app()->isProduction()) {
            return response()->json(['message' => 'Webhook secret hash is not configured.'], 503);
        }

        if ($secretHash && ! hash_equals($secretHash, (string) $request->header('verif-hash'))) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $payload = $request->all();
        $data = $payload['data'] ?? [];

        if (($data['status'] ?? '') !== 'successful') {
            return response()->json(['message' => 'Ignored non-successful event.']);
        }

        $txRef = (string) ($data['tx_ref'] ?? '');
        $payment = $txRef !== '' ? Payment::where('tx_ref', $txRef)->first() : null;
        $user = $payment?->user;

        if (! $payment) {
            $email = strtolower(trim((string) data_get($data, 'customer.email', '')));
            $user = $email !== '' ? User::where('email', $email)->first() : null;

            if (! $user || ! $user->billing_auto_renew || ! in_array($user->tier, ['starter', 'pro'], true)) {
                return response()->json(['message' => 'No matching auto-renew subscription.']);
            }

            $plan = $user->billing_plan ?: 'monthly';
            $payment = Payment::create([
                'user_id' => $user->id,
                'provider' => 'flutterwave',
                'tx_ref' => $txRef ?: 'POSTSMITH_RENEWAL_'.$user->id.'_'.Str::upper(Str::random(24)),
                'provider_transaction_id' => ($data['id'] ?? null) ? (string) $data['id'] : null,
                'status' => 'pending',
                'purpose' => 'subscription_renewal',
                'tier' => $user->tier,
                'plan' => $plan,
                'amount' => (float) ($data['amount'] ?? $this->expectedAmount($user->tier, $plan)),
                'currency' => (string) ($data['currency'] ?? config('postsmith.payments.currency')),
                'auto_renew_requested' => true,
            ]);
        }

        if ($payment->status !== 'paid' && $user) {
            $this->activatePayment($payment, $user, $data);
        }

        return response()->json(['message' => 'Webhook processed.']);
    }

    private function activatePayment(Payment $payment, User $user, array $data): void
    {
        $card = (array) ($data['card'] ?? []);
        $customer = (array) ($data['customer'] ?? []);
        $cardLastFour = $card['last_4digits'] ?? $card['last4'] ?? null;
        $cardBrand = $card['type'] ?? $card['brand'] ?? null;
        $cardExpiry = trim((string) (($card['expiry'] ?? '') ?: (($card['exp_month'] ?? '').'/'.($card['exp_year'] ?? ''))), '/');

        $payment->forceFill([
            'provider_transaction_id' => ($data['id'] ?? null) ? (string) $data['id'] : $payment->provider_transaction_id,
            'status' => 'paid',
            'amount' => (float) ($data['amount'] ?? $payment->amount),
            'currency' => (string) ($data['currency'] ?? $payment->currency),
            'payment_method' => $data['payment_type'] ?? $data['payment_method'] ?? null,
            'card_brand' => $cardBrand,
            'card_last_four' => $cardLastFour,
            'card_expiry' => $cardExpiry ?: null,
            'provider_customer_id' => ($customer['id'] ?? null) ? (string) $customer['id'] : $payment->provider_customer_id,
            'paid_at' => now(),
            'raw_payload' => $data,
        ])->save();

        $user->forceFill([
            'tier' => $payment->tier,
            'pro_expires_at' => $payment->plan === 'annual' ? now()->addYear() : now()->addMonth(),
            'billing_auto_renew' => $payment->auto_renew_requested,
            'billing_plan' => $payment->plan,
            'payment_provider_customer_id' => $payment->provider_customer_id,
            'billing_card_brand' => $cardBrand ?: $user->billing_card_brand,
            'billing_card_last_four' => $cardLastFour ?: $user->billing_card_last_four,
            'billing_card_expires' => $cardExpiry ?: $user->billing_card_expires,
            'billing_card_updated_at' => ($cardBrand || $cardLastFour) ? now() : $user->billing_card_updated_at,
            'downgraded_at' => null,
            'followup_count' => 0,
            'last_followup_sent' => null,
            'generations_used' => 0,
            'viral_reset_at' => now(),
        ])->save();

        $this->sendPaymentReceipt($payment, $user);
    }

    private function expectedAmount(string $tier, string $plan): float
    {
        return (float) config("postsmith.tiers.{$tier}.checkout_{$plan}_price", 0);
    }

    private function sendPaymentReceipt(Payment $payment, User $user): void
    {
        try {
            Mail::html(
                view('emails.payment-receipt', ['payment' => $payment, 'user' => $user])->render(),
                fn ($message) => $message
                    ->to($user->email, $user->name)
                    ->subject('Your PostSmith payment is confirmed')
            );
        } catch (\Throwable) {
            report('Could not send payment receipt for payment '.$payment->id);
        }
    }
}
