@php
    $isRegister = $mode === 'register';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isRegister ? 'Sign Up' : 'Sign In' }} - PostSmith</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #f8fafc; }
        .logo-text { font-weight: 700; letter-spacing: -0.03em; }
    </style>
</head>
<body class="min-h-screen text-slate-900">
    <main class="min-h-screen grid lg:grid-cols-[1fr_0.95fr]">
        <section class="hidden lg:flex bg-slate-950 text-white px-12 py-10 flex-col justify-between">
            <a href="{{ route('dashboard') }}" class="logo-text text-2xl inline-flex items-center gap-3">
                <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-11 h-11 object-contain">
                <span>PostSmith</span>
            </a>

            <div class="max-w-xl">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-indigo-300 mb-4">Creator workspace</p>
                <h1 class="logo-text text-5xl leading-tight">Turn rough thoughts into posts people respond to.</h1>
                <p class="text-slate-300 text-lg leading-8 mt-5">Save your best posts, track engagement, discover winning drivers, and keep your content engine organized.</p>
            </div>

            <div class="grid grid-cols-3 gap-3 text-sm">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="text-2xl font-bold">20</div>
                    <div class="text-slate-400">free generations</div>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="text-2xl font-bold">1</div>
                    <div class="text-slate-400">Viral Lab scan</div>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="text-2xl font-bold">7d</div>
                    <div class="text-slate-400">history</div>
                </div>
            </div>
        </section>

        <section class="px-5 py-8 sm:px-8 flex items-center justify-center">
            <div class="w-full max-w-md">
                <div class="lg:hidden mb-8">
                    <a href="{{ route('dashboard') }}" class="logo-text text-2xl inline-flex items-center gap-2.5">
                        <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-10 h-10 object-contain">
                        <span>PostSmith</span>
                    </a>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/60">
                    <div class="mb-6">
                        <p class="text-sm font-bold text-indigo-600 uppercase tracking-wide">{{ $isRegister ? 'Create account' : 'Welcome back' }}</p>
                        <h2 class="logo-text text-3xl text-slate-950 mt-1">{{ $isRegister ? 'Sign up for free' : 'Sign in to PostSmith' }}</h2>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm font-semibold">{{ $errors->first() }}</div>
                    @endif

                    @if (session('status'))
                        <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-semibold">{{ session('status') }}</div>
                    @endif

                    <a href="{{ route('auth.google.redirect') }}" class="w-full border border-slate-300 bg-white text-slate-700 px-4 py-3 rounded-lg font-bold hover:bg-slate-50 transition flex items-center justify-center gap-2">
                        <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        {{ $isRegister ? 'Sign up with Google' : 'Sign in with Google' }}
                    </a>

                    <div class="flex items-center gap-3 my-6">
                        <div class="h-px bg-slate-200 flex-1"></div>
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-400">or</span>
                        <div class="h-px bg-slate-200 flex-1"></div>
                    </div>

                    <form method="POST" action="{{ $isRegister ? route('register.store') : route('login.store') }}" class="space-y-4">
                        @csrf

                        @if ($isRegister)
                            <div>
                                <label for="name" class="block text-sm font-bold text-slate-700 mb-1.5">Name</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                            </div>
                        @endif

                        <div>
                            <label for="email" class="block text-sm font-bold text-slate-700 mb-1.5">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-bold text-slate-700 mb-1.5">Password</label>
                            <input id="password" name="password" type="password" autocomplete="{{ $isRegister ? 'new-password' : 'current-password' }}" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                        </div>

                        @if ($isRegister)
                            <div>
                                <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-1.5">Confirm password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                            </div>
                        @else
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                Remember me
                            </label>
                        @endif

                        <button type="submit" class="w-full bg-indigo-600 text-white font-bold px-4 py-3 rounded-lg hover:bg-indigo-700 transition">
                            {{ $isRegister ? 'Create account' : 'Sign in' }}
                        </button>
                    </form>

                    <p class="text-sm text-slate-500 text-center mt-6">
                        @if ($isRegister)
                            Already have an account?
                            <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Sign in</a>
                        @else
                            New to PostSmith?
                            <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Create an account</a>
                        @endif
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
