<!DOCTYPE html>
@php
    $adminDark = request()->routeIs('admin.*');
@endphp
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
        body { font-family: 'Space Grotesk', sans-serif; background: #ffffff; }
        body.admin-theme { background: #0f172a; }
        .logo-text { font-weight: 700; letter-spacing: -0.03em; }
        .admin-surface { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: none; border-radius: 12px !important; }
        .admin-muted-surface { background: #f8fafc; border: 1px solid #e2e8f0; }
        .admin-input { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; }
        .admin-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.18); }
        .admin-input option { background: #ffffff; color: #0f172a; }
        .admin-table-head { background: #f8fafc; color: #64748b; }
        .admin-row { border-color: #e2e8f0; }
        .admin-row:hover { background: #f8fafc; }
        .pagination { color: #475569; }
        .pagination a, .pagination span { border-color: #cbd5e1 !important; background-color: #ffffff !important; color: #475569 !important; }
        .pagination [aria-current="page"] span { background-color: #4f46e5 !important; color: #fff !important; border-color: #6366f1 !important; }
        .admin-theme .admin-surface { background: #111827; border: 1px solid #263244; box-shadow: none; }
        .admin-theme .admin-muted-surface { background: #162033; border-color: #263244; }
        .admin-theme .admin-input { background: #0f172a; border: 1px solid #334155; color: #e2e8f0; }
        .admin-theme .admin-input option { background: #0f172a; color: #e2e8f0; }
        .admin-theme .admin-table-head { background: #0f172a; color: #94a3b8; }
        .admin-theme .admin-row { border-color: #263244; }
        .admin-theme .admin-row:hover { background: #162033; }
        .admin-theme .pagination { color: #cbd5e1; }
        .admin-theme .pagination a, .admin-theme .pagination span { border-color: #334155 !important; background-color: #0f172a !important; color: #cbd5e1 !important; }
    </style>
    @if ($adminDark)
        <link rel="stylesheet" href="{{ asset('css/admin-dark.css') }}">
    @endif
    </head>
    <body class="{{ $adminDark ? 'admin-theme text-slate-100' : 'text-slate-900' }} antialiased">
    <div class="min-h-screen">
        <header class="{{ $adminDark ? 'bg-slate-900/95 border-slate-800' : 'bg-white/95 border-slate-200' }} backdrop-blur border-b sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
                <a href="{{ route('admin.dashboard') }}" class="logo-text text-xl {{ $adminDark ? 'text-white' : 'text-slate-950' }} inline-flex items-center gap-2.5">
                    <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-9 h-9 object-contain shrink-0">
                    <span>PostSmith Admin</span>
                </a>
                <nav class="hidden md:flex items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? ($adminDark ? 'bg-indigo-500/15 text-indigo-200' : 'bg-indigo-50 text-indigo-700') : ($adminDark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-950') }}">Overview</a>
                    <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.users') ? ($adminDark ? 'bg-indigo-500/15 text-indigo-200' : 'bg-indigo-50 text-indigo-700') : ($adminDark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-950') }}">Users</a>
                    <a href="{{ route('admin.drivers') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.drivers') ? ($adminDark ? 'bg-indigo-500/15 text-indigo-200' : 'bg-indigo-50 text-indigo-700') : ($adminDark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-950') }}">Drivers</a>
                    <a href="{{ route('admin.viral-lab') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.viral-lab') ? ($adminDark ? 'bg-indigo-500/15 text-indigo-200' : 'bg-indigo-50 text-indigo-700') : ($adminDark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-950') }}">Viral Lab</a>
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg {{ $adminDark ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-950' }}">App</a>
                </nav>
                <a href="{{ route('dashboard') }}" class="md:hidden text-sm font-bold {{ $adminDark ? 'text-indigo-100 bg-indigo-500/15 border-indigo-400/20' : 'text-indigo-700 bg-indigo-50 border-indigo-200' }} border px-3 py-2 rounded-lg">App</a>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
            @if (session('status'))
                <div class="mb-5 {{ $adminDark ? 'bg-emerald-500/10 border-emerald-400/20 text-emerald-200' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }} border px-4 py-3 rounded-xl text-sm font-semibold">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-5 {{ $adminDark ? 'bg-red-500/10 border-red-400/20 text-red-200' : 'bg-red-50 border-red-200 text-red-800' }} border px-4 py-3 rounded-xl text-sm font-semibold">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
