<div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;color:#111827">
    <h1 style="font-size:24px;margin-bottom:12px">Verify your PostSmith email</h1>
    <p style="font-size:15px;line-height:1.6">Hi {{ $user->name ?: 'there' }}, use this code to verify your account:</p>
    <div style="font-size:32px;font-weight:700;letter-spacing:8px;background:#f1f5f9;border-radius:12px;padding:18px 22px;text-align:center;margin:24px 0">{{ $code }}</div>
    <p style="font-size:14px;line-height:1.6;color:#4b5563">This code expires in 30 minutes. If you did not create a PostSmith account, you can ignore this email.</p>
</div>
