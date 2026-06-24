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
        body { font-family: 'Space Grotesk', sans-serif; background: #071126; }
        .logo-text { font-weight: 700; letter-spacing: -0.03em; }
        .admin-surface { background: #071324; border: 1px solid rgba(255,255,255,0.06); box-shadow: 0 18px 50px rgba(2,6,23,0.45); }
        .admin-muted-surface { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06); }
        .admin-input { background: rgba(15,23,42,0.95); border: 1px solid #334155; color: #e2e8f0; }
        .admin-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.18); }
        .admin-input option { background: #0f172a; color: #e2e8f0; }
        .admin-table-head { background: rgba(15,23,42,0.92); color: #94a3b8; }
        .admin-row { border-color: #1e293b; }
        .admin-row:hover { background: rgba(30,41,59,0.6); }
        .pagination { color: #cbd5e1; }
        .pagination a, .pagination span { border-color: #334155 !important; background-color: #0f172a !important; color: #cbd5e1 !important; }
        .pagination [aria-current="page"] span { background-color: #4f46e5 !important; color: #fff !important; border-color: #6366f1 !important; }
    </style>
</head>
<body class="text-slate-100 antialiased">
    <div class="min-h-screen">
        <header class="bg-[#071324]/95 backdrop-blur border-b border-white/5 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
                <a href="{{ route('admin.dashboard') }}" class="logo-text text-xl text-white inline-flex items-center gap-2.5">
                    <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-9 h-9 object-contain shrink-0">
                    <span>PostSmith Admin</span>
                </a>
                <nav class="hidden md:flex items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-500/15 text-indigo-200' : 'text-slate-400 hover:text-white' }}">Overview</a>
                    <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.users') ? 'bg-indigo-500/15 text-indigo-200' : 'text-slate-400 hover:text-white' }}">Users</a>
                    <a href="{{ route('admin.drivers') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.drivers') ? 'bg-indigo-500/15 text-indigo-200' : 'text-slate-400 hover:text-white' }}">Drivers</a>
                    <a href="{{ route('admin.viral-lab') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('admin.viral-lab') ? 'bg-indigo-500/15 text-indigo-200' : 'text-slate-400 hover:text-white' }}">Viral Lab</a>
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-slate-400 hover:text-white">App</a>
                </nav>
                <a href="{{ route('dashboard') }}" class="md:hidden text-sm font-bold text-indigo-100 bg-indigo-500/15 border border-indigo-400/20 px-3 py-2 rounded-lg">App</a>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
            @if (session('status'))
                <div class="mb-5 bg-emerald-500/10 border border-emerald-400/20 text-emerald-200 px-4 py-3 rounded-xl text-sm font-semibold">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-5 bg-red-500/10 border border-red-400/20 text-red-200 px-4 py-3 rounded-xl text-sm font-semibold">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
