<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - PostSmith</title>
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
<body class="text-slate-800">
    <div class="min-h-screen">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
                <a href="{{ route('admin.dashboard') }}" class="logo-text text-xl text-slate-950 inline-flex items-center gap-2.5">
                    <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-9 h-9 object-contain shrink-0">
                    <span>PostSmith Admin</span>
                </a>
                <nav class="hidden md:flex items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Overview</a>
                    <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.users') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Users</a>
                    <a href="{{ route('admin.drivers') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.drivers') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Drivers</a>
                    <a href="{{ route('admin.viral-lab') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.viral-lab') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-slate-900' }}">Viral Lab</a>
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-slate-500 hover:text-slate-900">App</a>
                </nav>
                <a href="{{ route('dashboard') }}" class="md:hidden text-sm font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-2 rounded-lg">App</a>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
            @if (session('status'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-semibold">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm font-semibold">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
