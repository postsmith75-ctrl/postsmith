<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - PostSmith</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Space Grotesk',sans-serif;background:#f8fafc}.logo-text{font-weight:700;letter-spacing:-.03em}</style>
</head>
<body class="min-h-screen text-slate-900 flex items-center justify-center px-5">
    <main class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/60">
        <a href="{{ route('dashboard') }}" class="logo-text text-2xl inline-flex items-center gap-2.5 mb-8">
            <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-10 h-10 object-contain">
            <span>PostSmith</span>
        </a>

        <p class="text-sm font-bold text-indigo-600 uppercase tracking-wide">Verify email</p>
        <h1 class="logo-text text-3xl text-slate-950 mt-1">Check your inbox</h1>
        <p class="text-sm text-slate-500 mt-3 leading-6">Enter the 6-digit code sent to <strong>{{ auth()->user()->email }}</strong>.</p>

        @if ($errors->any())
            <div class="mt-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm font-semibold">{{ $errors->first() }}</div>
        @endif

        @if (session('status'))
            <div class="mt-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-semibold">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.verify') }}" class="space-y-4 mt-6">
            @csrf
            <div>
                <label for="code" class="block text-sm font-bold text-slate-700 mb-1.5">Verification code</label>
                <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition text-center text-2xl tracking-[0.35em] font-bold">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold px-4 py-3 rounded-lg hover:bg-indigo-700 transition">Verify email</button>
        </form>

        <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full bg-slate-100 text-slate-700 font-bold px-4 py-3 rounded-lg hover:bg-slate-200 transition">Resend code</button>
        </form>
    </main>
</body>
</html>
