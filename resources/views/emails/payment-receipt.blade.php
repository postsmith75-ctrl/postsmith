<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;color:#111827">
    <h1 style="font-size:24px;margin-bottom:12px">Payment confirmed</h1>
    <p style="font-size:15px;line-height:1.6">Hi {{ $user->name ?: 'there' }}, your PostSmith {{ ucfirst($payment->tier) }} {{ $payment->plan }} payment is confirmed.</p>
    <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:14px">
        <tr><td style="padding:10px;border-bottom:1px solid #e5e7eb;color:#6b7280">Amount</td><td style="padding:10px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:700">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td></tr>
        <tr><td style="padding:10px;border-bottom:1px solid #e5e7eb;color:#6b7280">Reference</td><td style="padding:10px;border-bottom:1px solid #e5e7eb;text-align:right">{{ $payment->tx_ref }}</td></tr>
        <tr><td style="padding:10px;border-bottom:1px solid #e5e7eb;color:#6b7280">Auto-renew</td><td style="padding:10px;border-bottom:1px solid #e5e7eb;text-align:right">{{ $payment->auto_renew_requested ? 'Enabled' : 'Disabled' }}</td></tr>
    </table>
    <p style="font-size:14px;line-height:1.6;color:#4b5563">PostSmith never stores your full card number or CVV. Card details stay with Flutterwave.</p>
</div>
