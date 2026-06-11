@php
    $activeTab = session('active_tab', session('rewrites') ? 'rewrite' : 'scratch');
    $items = $generated ?: $rewrites;
    $platform = old('platform', 'Facebook Groups/Feed');
    $length = old('length', 'medium');

    $icon = function (string $name, int $size = 18, string $class = '') {
        $attrs = "width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"{$class}\"";
        return match ($name) {
            'rocket' => "<svg {$attrs}><path d=\"M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z\"/><path d=\"m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z\"/><path d=\"M9 12H4s.55-3.03 2-4c1.62-1.08 3 0 3 0\"/><path d=\"M12 15v5s3.03-.55 4-2c1.08-1.62 0-3 0-3\"/></svg>",
            'pen' => "<svg {$attrs}><path d=\"M12 20h9\"/><path d=\"M16.5 3.5a2.12 2.83 0 1 1 3 3L7 19l-4 1 1-4Z\"/></svg>",
            'flask' => "<svg {$attrs}><path d=\"M10 2v7.31\"/><path d=\"M14 2v7.31\"/><path d=\"M8.5 2h7\"/><path d=\"M14 9.3a6.5 6.5 0 1 1-4 0\"/><path d=\"M5.52 16h12.96\"/></svg>",
            'clipboard' => "<svg {$attrs}><rect width=\"14\" height=\"20\" x=\"5\" y=\"2\" rx=\"2\" ry=\"2\"/><path d=\"M9 2v4\"/><path d=\"M15 2v4\"/><path d=\"M9 12h6\"/><path d=\"M9 16h6\"/></svg>",
            'save' => "<svg {$attrs}><path d=\"M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z\"/><path d=\"M17 21v-8H7v8\"/><path d=\"M7 3v5h8\"/></svg>",
            'lock' => "<svg {$attrs}><rect width=\"18\" height=\"11\" x=\"3\" y=\"11\" rx=\"2\" ry=\"2\"/><path d=\"M7 11V7a5 5 0 0 1 10 0v4\"/></svg>",
            'chart' => "<svg {$attrs}><path d=\"M3 3v16a2 2 0 0 0 2 2h16\"/><path d=\"m19 9-5 5-4-4-3 3\"/></svg>",
            'file' => "<svg {$attrs}><path d=\"M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z\"/><path d=\"M14 2v4a2 2 0 0 0 2 2h4\"/></svg>",
            'check' => "<svg {$attrs}><path d=\"M20 6 9 17l-5-5\"/></svg>",
            'star' => "<svg {$attrs}><polygon points=\"12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\"/></svg>",
            'search' => "<svg {$attrs}><circle cx=\"11\" cy=\"11\" r=\"8\"/><path d=\"m21 21-4.3-4.3\"/></svg>",
            default => '',
        };
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PostSmith - Turn Any Thought Into a Post That Gets Likes, Comments & Shares</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://checkout.flutterwave.com/v3.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background:
                radial-gradient(circle at 18% 8%, rgba(79,70,229,.12), transparent 28%),
                radial-gradient(circle at 82% 18%, rgba(16,185,129,.10), transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, #eef2ff 34%, #f8fafc 70%),
                #f8fafc;
        }
        .logo-text { font-weight: 700; letter-spacing: -0.03em; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 16px 32px -8px rgba(0,0,0,0.12); }
        .copy-btn { transition: all 0.2s; }
        .copy-btn:active { transform: scale(0.96); }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); } 50% { box-shadow: 0 0 20px 4px rgba(99, 102, 241, 0.2); } }
        @keyframes commentSlide { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        .pro-wall-overlay { background: rgba(49, 46, 129, 0.55) !important; backdrop-filter: blur(4px) !important; }
        .pro-wall-card { animation: pulse-glow 2s ease-in-out infinite; }
        .analysis-box { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
        .post-text { line-height: 1.65; }
        .glass-nav { background: rgba(255,255,255,0.82); backdrop-filter: blur(18px); box-shadow: 0 1px 0 rgba(148,163,184,.22); }
        .hero-shell {
            border: 1px solid rgba(199,210,254,.78);
            background: linear-gradient(135deg, rgba(255,255,255,.96), rgba(248,250,252,.92));
            box-shadow: 0 28px 80px -40px rgba(49,46,129,.55);
            position: relative;
            overflow: hidden;
        }
        .hero-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(79,70,229,.08), transparent 32%, rgba(16,185,129,.08)),
                repeating-linear-gradient(90deg, rgba(15,23,42,.035) 0 1px, transparent 1px 72px);
            pointer-events: none;
        }
        .premium-panel {
            border: 1px solid rgba(226,232,240,.95);
            background: rgba(255,255,255,.94);
            box-shadow: 0 22px 60px -36px rgba(15,23,42,.55);
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #c7d2fe;
            background: #eef2ff;
            color: #3730a3;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
        }
        .proof-card {
            border: 1px solid rgba(226,232,240,.9);
            background: rgba(255,255,255,.76);
            border-radius: 14px;
            padding: 14px;
        }
        .comment-rail {
            border: 1px solid rgba(226,232,240,.9);
            background: rgba(255,255,255,.68);
            border-radius: 18px;
            overflow: hidden;
            position: relative;
        }
        .comment-rail::before,
        .comment-rail::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            width: 44px;
            z-index: 2;
            pointer-events: none;
        }
        .comment-rail::before { left: 0; background: linear-gradient(90deg, rgba(255,255,255,.96), transparent); }
        .comment-rail::after { right: 0; background: linear-gradient(270deg, rgba(255,255,255,.96), transparent); }
        .comment-track {
            display: flex;
            gap: 12px;
            width: max-content;
            animation: commentSlide 28s linear infinite;
            padding: 12px;
        }
        .comment-rail:hover .comment-track { animation-play-state: paused; }
        .comment-card {
            width: 260px;
            border: 1px solid rgba(226,232,240,.9);
            background: white;
            border-radius: 14px;
            padding: 14px;
            box-shadow: 0 12px 26px -22px rgba(15,23,42,.65);
        }
        .avatar-dot {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            color: white;
            font-size: 11px;
            font-weight: 700;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
        }
        .signal-card {
            border: 1px solid rgba(226,232,240,.95);
            background: rgba(255,255,255,.78);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 18px 45px -35px rgba(15,23,42,.6);
        }
        .pricing-card { transition: all 0.2s; border: 2px solid transparent; }
        .pricing-card:hover { border-color: #6366f1; transform: translateY(-2px); }
        .workspace-card {
            border: 1px solid rgba(226,232,240,.95);
            background: rgba(255,255,255,.86);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 18px 50px -38px rgba(15,23,42,.65);
        }
        .before-after {
            border: 1px solid rgba(226,232,240,.95);
            background: rgba(255,255,255,.84);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 22px 60px -42px rgba(15,23,42,.7);
        }
        .tool-header {
            background: linear-gradient(135deg, #111827, #312e81);
            color: white;
            border-radius: 18px 18px 0 0;
        }
        .tab-bar { background: #f1f5f9; border-radius: 16px; padding: 6px; display: flex; gap: 4px; max-width: none; }
        .tab-btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 500; color: #64748b; transition: all 0.2s; border: none; background: transparent; cursor: pointer; }
        .tab-btn.active { background: white; color: #4338ca; box-shadow: 0 1px 3px rgba(0,0,0,0.08); font-weight: 600; }
        .tab-btn:hover:not(.active) { color: #334155; }
        #loading-overlay.active { opacity: 1; pointer-events: auto; }
        .forge-chip { transition: all 0.2s ease; }
        .forge-chip:hover { transform: translateY(-1px); }
        .forge-chip input:checked + span { background: #4f46e5; color: white; border-color: #4f46e5; }
        .tooltip { position: relative; }
        .tooltip .tooltiptext { visibility: hidden; width: 280px; background: #1e293b; color: #fff; text-align: left; border-radius: 8px; padding: 10px 12px; position: absolute; z-index: 50; bottom: 125%; left: 50%; margin-left: -140px; opacity: 0; transition: opacity 0.2s; font-size: 12px; line-height: 1.5; }
        .tooltip:hover .tooltiptext { visibility: visible; opacity: 1; }
        .usage-bar-bg { background: #e2e8f0; border-radius: 999px; height: 6px; overflow: hidden; }
        .usage-bar-fill { height: 100%; border-radius: 999px; transition: width 0.3s ease; }
        .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #1f2937; color: white; padding: 12px 24px; border-radius: 40px; font-size: 14px; font-weight: 500; z-index: 1000; opacity: 0; transition: opacity 0.2s ease; pointer-events: none; }
        .toast.show { opacity: 1; }
        .regen-btn { transition: all 0.2s ease; }
        .regen-btn:hover { background-color: #f5f3ff; border-color: #6366f1; color: #4f46e5; transform: translateY(-1px); }
        textarea, select { box-shadow: 0 1px 2px rgba(15,23,42,.04); }
        textarea::placeholder { color: #94a3b8; }
        @media (max-width: 640px) {
            .hero-shell { border-radius: 22px; }
            .tab-bar { display: grid; grid-template-columns: 1fr; }
            .tab-btn { justify-content: flex-start; padding: 12px 14px; }
            .comment-card { width: 230px; }
            .premium-panel { border-radius: 18px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .comment-track { animation: none; }
        }
    </style>
</head>
<body class="text-gray-800">
    <div id="loading-overlay" class="fixed inset-0 bg-white/95 backdrop-blur-sm z-[100] opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="flex flex-col items-center justify-center text-center w-full h-full">
            <div class="max-w-sm px-6">
                <div class="w-[200px] h-[200px] mx-auto mb-[30px] rounded-full bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 flex items-center justify-center shadow-lg shadow-indigo-500/10">
                    <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith" class="w-32 h-32 object-contain">
                </div>
                <h3 class="text-xl font-bold logo-text text-gray-900 mb-2 mt-1">Forging your post...</h3>
                <p class="text-sm text-gray-500 font-medium min-h-[24px]">Finding the hook, rhythm, and driver.</p>
            </div>
        </div>
    </div>

    <div class="glass-nav sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 py-4 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="logo-text text-xl sm:text-2xl text-slate-950 flex items-center gap-2.5">
                <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-9 h-9 sm:w-10 sm:h-10 object-contain shrink-0">
                <span>PostSmith</span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3">
                @guest
                    <a href="#pricing" class="hidden sm:inline text-sm text-slate-500 font-semibold hover:text-indigo-700 transition mr-1">Pricing</a>
                @endguest
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-xs sm:text-sm text-amber-700 bg-amber-50 border border-amber-200 px-2.5 sm:px-3 py-1.5 rounded-lg font-bold hover:bg-amber-100 transition">Admin</a>
                    @endif
                    <span class="hidden sm:inline text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs sm:text-sm bg-gray-900 text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg font-medium hover:bg-gray-800 transition">Logout</button>
                    </form>
                @else
                    <a href="{{ route('auth.google.redirect') }}" class="text-xs sm:text-sm bg-white border border-gray-300 text-gray-700 px-2 sm:px-4 py-1.5 sm:py-2 rounded-lg font-medium hover:bg-gray-50 transition flex items-center gap-1.5 sm:gap-2">
                        <svg class="w-4 h-4 sm:w-[18px] sm:h-[18px]" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        <span class="hidden sm:inline">Sign in with Google</span><span class="sm:hidden">Sign in</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-2 sm:px-6 py-5 sm:py-8">
        <div id="toast-message" class="toast"></div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 alert-banner relative pr-10 flex items-center gap-2">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('status'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 fade-in alert-banner relative pr-10 flex items-center gap-2">
                {!! $icon('check', 16) !!} {{ session('status') }}
            </div>
        @endif

        @auth
            <section class="mb-8">
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">Creator dashboard</p>
                        <h1 class="text-3xl sm:text-4xl font-bold logo-text text-slate-950 mt-2">Welcome back, {{ auth()->user()->name ?: 'creator' }}.</h1>
                        <p class="text-slate-500 mt-2">Generate, save, track, and learn which post formats deserve more of your time.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $usage['label'] ?? 'Free' }}</span>
                        @if (auth()->user()->isPro())
                            <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-purple-50 text-purple-700 border border-purple-100">Pro</span>
                        @elseif (auth()->user()->isStarter())
                            <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">Starter</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4">
                    <div class="workspace-card">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Posts tracked</p>
                        <p class="text-2xl font-bold text-slate-950 mt-2">{{ number_format($stats['posts']) }}</p>
                    </div>
                    <div class="workspace-card">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Likes</p>
                        <p class="text-2xl font-bold text-blue-700 mt-2">{{ number_format($stats['likes']) }}</p>
                    </div>
                    <div class="workspace-card">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Comments</p>
                        <p class="text-2xl font-bold text-emerald-700 mt-2">{{ number_format($stats['comments']) }}</p>
                    </div>
                    <div class="workspace-card">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">Shares</p>
                        <p class="text-2xl font-bold text-purple-700 mt-2">{{ number_format($stats['shares']) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="workspace-card">
                        <p class="text-sm font-bold text-slate-950 mb-2">Performance signal</p>
                        <p class="text-sm text-slate-500 leading-6">
                            @if ($stats['best_driver'])
                                Your best current driver is <strong class="text-slate-900">{{ $stats['best_driver'] }}</strong>{{ $stats['best_platform'] ? ' on '.$stats['best_platform'] : '' }}. Generate more angles around that pattern.
                            @else
                                Save posts and log results to reveal your strongest driver, platform, and length.
                            @endif
                        </p>
                    </div>
                    <div class="workspace-card">
                        <p class="text-sm font-bold text-slate-950 mb-2">Monthly usage</p>
                        <div class="flex justify-between text-xs text-slate-500 font-semibold mb-2">
                            <span>{{ number_format($usage['used'] ?? 0) }} used</span>
                            <span>{{ ($usage['limit'] ?? -1) < 0 ? 'Unlimited' : number_format($usage['limit']) }}</span>
                        </div>
                        <div class="usage-bar-bg">
                            @php
                                $usageLimit = $usage['limit'] ?? 0;
                                $usagePercent = $usageLimit > 0 ? min(100, round((($usage['used'] ?? 0) / $usageLimit) * 100)) : 0;
                            @endphp
                            <div class="usage-bar-fill bg-indigo-600" style="width: {{ $usagePercent }}%"></div>
                        </div>
                    </div>
                    <div class="workspace-card">
                        <p class="text-sm font-bold text-slate-950 mb-2">Direct publish</p>
                        <p class="text-sm text-slate-500 leading-6">
                            @if (auth()->user()->zernio_api_key)
                                Zernio is connected. Pro publishing can be routed from saved posts.
                            @else
                                Zernio is not connected yet. We can add a settings page next so this feels native.
                            @endif
                        </p>
                    </div>
                </div>
            </section>
        @endauth

        <section class="hero-shell rounded-[28px] p-3 sm:p-6 lg:p-8 mb-8">
            <div class="relative grid grid-cols-1 lg:grid-cols-[0.9fr_1.1fr] gap-6 lg:gap-8 items-start">
                <div class="pt-1 lg:pt-6">
                    @auth
                        <div class="eyebrow mb-5">{!! $icon('star', 14) !!} Creator workbench</div>
                    @endauth
                    <h1 class="text-[2.35rem] sm:text-5xl lg:text-6xl font-bold logo-text text-slate-950 leading-[0.98] max-w-2xl">{{ auth()->check() ? 'Forge your next post with data behind it.' : 'Turn rough thoughts into posts people like, comment and share.' }}</h1>
                    <p class="text-slate-600 text-base sm:text-lg leading-7 sm:leading-8 max-w-xl mt-5">{{ auth()->check() ? 'Use your best-performing patterns, save the winners, and keep building a content system around what actually gets responses.' : "Dump what's in your head. PostSmith finds the emotion, structure, and hook, then gives you polished ways to say it so people actually respond." }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-8 max-w-2xl">
                        <div class="proof-card">
                            <p class="text-2xl font-bold text-slate-950">5</p>
                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide mt-1">Driver angles</p>
                        </div>
                        <div class="proof-card">
                            <p class="text-2xl font-bold text-emerald-700">3x</p>
                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide mt-1">Cleaner testing</p>
                        </div>
                        <div class="proof-card">
                            <p class="text-2xl font-bold text-indigo-700">1</p>
                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide mt-1">Raw thought</p>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-2 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/80 border border-slate-200 px-3 py-1.5">{!! $icon('check', 12, 'text-emerald-500') !!} No polishing first</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/80 border border-slate-200 px-3 py-1.5">{!! $icon('check', 12, 'text-emerald-500') !!} Platform-aware</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/80 border border-slate-200 px-3 py-1.5">{!! $icon('check', 12, 'text-emerald-500') !!} Track what works</span>
                    </div>

                </div>

                <div class="premium-panel rounded-[22px] overflow-hidden">
                    <div class="tool-header px-5 sm:px-6 py-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-white/80">Start with a thought, draft, or viral example.</p>
                        </div>
                        <div class="hidden sm:flex items-center gap-2 text-xs text-white/70">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Ready
                        </div>
                    </div>
                    <div class="p-3 sm:p-6">
                        <div class="tab-bar mb-6">
                            <button onclick="switchMode('scratch')" id="tab-scratch" class="tab-btn {{ $activeTab === 'scratch' ? 'active' : '' }}">{!! $icon('rocket',18) !!}<span>I Have a Thought</span></button>
                            <button onclick="switchMode('rewrite')" id="tab-rewrite" class="tab-btn {{ $activeTab === 'rewrite' ? 'active' : '' }}">{!! $icon('pen',18) !!}<span>Fix My Draft</span></button>
                            <button onclick="switchMode('viral_lab')" id="tab-viral_lab" class="tab-btn {{ $activeTab === 'viral_lab' ? 'active' : '' }}">{!! $icon('flask',18) !!}<span>Viral Lab</span></button>
                        </div>

                        <div class="text-xs text-slate-500 mb-5">
                            By using PostSmith, you agree to our <a href="{{ route('terms') }}" class="text-indigo-600 hover:underline font-medium">Terms</a> and have read our <a href="{{ route('privacy') }}" class="text-indigo-600 hover:underline font-medium">Privacy Policy</a>.
                        </div>

                        <div id="mode-scratch" class="{{ $activeTab === 'scratch' ? 'fade-in' : 'hidden fade-in' }}">
            <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-3 sm:p-6">
                <form method="POST" action="{{ route('generate') }}" class="space-y-5" id="form-generate">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">What's on your mind?</label>
                        <textarea name="thought" rows="4" placeholder="A feeling, a question, a rant, a win, a struggle - even one sentence. Example: 'I'm tired of posting everyday and getting zero comments back'" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition resize-y text-base" required>{{ old('thought') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1.5">No need to be polished. Raw thoughts work best.</p>
                    </div>
                    <div>
                        <button type="button" onclick="toggleAdvanced('scratch-advanced')" class="text-sm text-indigo-600 font-medium flex items-center gap-1 hover:text-indigo-700 transition"><span id="scratch-advanced-icon">+</span> Choose platform & length</button>
                        <div id="scratch-advanced" class="hidden mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Platform</label>
                                <select name="platform" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition bg-white">
                                    @foreach ($platforms as $p)
                                        <option value="{{ $p }}" @selected($platform === $p)>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Length</label>
                                <select name="length" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition bg-white">
                                    <option value="short" @selected($length === 'short')>Short - Quick hit (2-3 lines)</option>
                                    <option value="medium" @selected($length === 'medium')>Medium - Natural flow (8-12 lines)</option>
                                    <option value="long" @selected($length === 'long')>Long - Full story (5-7 paragraphs)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <label class="block text-sm font-semibold text-gray-700">Your Forge</label>
                            <span class="text-xs font-normal text-gray-400">(select up to 5)</span>
                        </div>
                        <div class="flex flex-wrap gap-2" id="forge-selector-scratch">
                            @foreach ($drivers as $driver)
                                <label class="forge-chip cursor-pointer select-none">
                                    <input type="checkbox" name="drivers[]" value="{{ $driver }}" class="hidden" checked onchange="updateForgeSelection('forge-selector-scratch','gen-count-scratch')">
                                    <span class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold border transition bg-indigo-600 text-white border-indigo-600">{{ $driver }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-1" id="gen-count-scratch">{{ count($drivers) }} of 5 selected</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" id="gen-btn-scratch" class="w-full sm:w-auto justify-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg hover:from-indigo-700 hover:to-purple-700 transition transform hover:-translate-y-0.5 flex items-center gap-2">{!! $icon('rocket',16) !!} Forge My Post</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="mode-rewrite" class="{{ $activeTab === 'rewrite' ? 'fade-in' : 'hidden fade-in' }}">
            <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-3 sm:p-6">
                <form method="POST" action="{{ route('rewrite') }}" class="space-y-5" id="form-rewrite">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Paste your draft</label>
                        <textarea name="draft" rows="5" placeholder="Even if it's messy, incomplete, or all over the place - just dump it here. PostSmith will figure out what you're trying to say and fix the structure." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition resize-y text-base" required>{{ old('draft') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1.5">Don't edit it first. The messier, the better we can help.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Platform</label>
                            <select name="platform" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition bg-white">
                                @foreach ($platforms as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Length</label>
                            <select name="length" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition bg-white">
                                <option value="short">Short - Quick hit</option>
                                <option value="medium" selected>Medium - Natural flow</option>
                                <option value="long">Long - Full story</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-2"><label class="block text-sm font-semibold text-gray-700">Your Forge</label><span class="text-xs font-normal text-gray-400">(select up to 5)</span></div>
                        <div class="flex flex-wrap gap-2" id="forge-selector-rewrite">
                            @foreach ($drivers as $driver)
                                <label class="forge-chip cursor-pointer select-none">
                                    <input type="checkbox" name="drivers[]" value="{{ $driver }}" class="hidden" checked onchange="updateForgeSelection('forge-selector-rewrite','gen-count-rewrite')">
                                    <span class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold border transition bg-indigo-600 text-white border-indigo-600">{{ $driver }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-1" id="gen-count-rewrite">{{ count($drivers) }} of 5 selected</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" id="gen-btn-rewrite" class="w-full sm:w-auto justify-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg hover:from-purple-700 hover:to-indigo-700 transition transform hover:-translate-y-0.5 flex items-center gap-2">{!! $icon('search',16) !!} Fix My Post</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="mode-viral_lab" class="{{ $activeTab === 'viral_lab' ? 'fade-in' : 'hidden fade-in' }}">
            <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-6 sm:p-8 text-center">
                <div class="flex justify-center mb-3">{!! $icon('flask',48,'text-gray-300') !!}</div>
                <h3 class="text-xl font-bold logo-text mb-2">Viral Lab</h3>
                <p class="text-gray-600 mb-4 max-w-md mx-auto">Paste a high-performing post you found on social media. Our AI breaks down the exact engagement drivers at work.</p>
                @auth
                    <form method="POST" action="{{ route('viral-lab.store') }}" class="space-y-4 text-left">
                        @csrf
                        <textarea name="post_text" rows="5" placeholder="Paste a post with proven engagement. Include enough context for the engine to detect the pattern." class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition resize-y text-base" required>{{ old('post_text') }}</textarea>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <select name="platform" class="sm:col-span-1 px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition bg-white">
                                @foreach ($platforms as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="likes" min="0" placeholder="Likes" class="px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition" required>
                            <input type="number" name="comments" min="0" placeholder="Comments" class="px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition" required>
                            <input type="number" name="shares" min="0" placeholder="Shares" class="px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition" required>
                        </div>
                        <button type="submit" class="w-full sm:w-auto justify-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg hover:from-purple-700 hover:to-indigo-700 transition transform hover:-translate-y-0.5 flex items-center gap-2">{!! $icon('flask',16) !!} Analyze Post</button>
                        <p class="text-xs text-gray-400">Minimums: {{ config('postsmith.viral_lab.min_words') }} words, {{ config('postsmith.viral_lab.min_likes') }} likes, {{ config('postsmith.viral_lab.min_comments') }} comments, {{ config('postsmith.viral_lab.min_shares') }} shares.</p>
                    </form>
                @else
                    <div class="bg-gray-50 rounded-xl p-4 mb-4 max-w-md mx-auto text-left text-sm text-gray-600 space-y-2">
                        <div class="flex items-center gap-2">{!! $icon('check',14,'text-green-500') !!}<span>Analyze real winning posts</span></div>
                        <div class="flex items-center gap-2">{!! $icon('check',14,'text-green-500') !!}<span>Discover new engagement drivers</span></div>
                        <div class="flex items-center gap-2">{!! $icon('check',14,'text-green-500') !!}<span>Auto-train the AI engine</span></div>
                    </div>
                    <a href="{{ route('auth.google.redirect') }}" class="bg-indigo-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-indigo-700 transition inline-block">Sign in with Google</a>
                    <p class="text-xs text-gray-400 mt-2">Free users get 1 analysis per month</p>
                @endauth
            </div>
        </div>
                    </div>
                </div>
            </div>
        </section>

        @if (session('viral_analysis'))
            @php($viralAnalysis = session('viral_analysis'))
            <section class="mb-8 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 fade-in">
                <div class="flex items-center justify-between gap-4 flex-wrap mb-4">
                    <h2 class="text-xl font-bold logo-text text-slate-950 flex items-center gap-2">{!! $icon('flask',18) !!} Viral Lab Analysis</h2>
                    @if (!empty($viralAnalysis['dominant_driver']))
                        <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $viralAnalysis['dominant_driver'] }}</span>
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide mb-2">Why it works</p>
                        <p class="text-sm text-slate-700 leading-6">{{ $viralAnalysis['why_it_works'] ?? 'PostSmith detected repeatable engagement patterns in this post.' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wide mb-2">Detected drivers</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (($viralAnalysis['detected_drivers'] ?? []) as $driver)
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-white text-indigo-700 border border-indigo-100">{{ $driver }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @guest
        <section class="before-after mb-8 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-[0.75fr_1.25fr] gap-0 bg-white border-b border-slate-200">
                <div class="p-5 sm:p-7 lg:p-8 flex flex-col justify-center">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600 mb-3">Before and after</p>
                    <h2 class="text-3xl sm:text-4xl font-bold logo-text text-slate-950 leading-tight">Great Ideas Deserve More Than 3 Likes.</h2>
                    <p class="text-slate-600 text-base sm:text-lg leading-7 mt-4">Most creators and businesses waste hours creating content that gets ignored.</p>
                    <p class="text-slate-700 text-base sm:text-lg leading-7 mt-4">PostSmith helps you create content people actually want to read by transforming your thoughts into content that builds authority, grows your audience, and drives engagement.</p>
                </div>
                <div class="p-3 sm:p-5">
                    <img
                        src="{{ asset('before-after-postsmith.png') }}"
                        alt="Before and after example showing a post improving from 35 reactions and 3 comments to 3,279 reactions and 245 comments"
                        class="w-full rounded-xl border border-slate-200 shadow-lg shadow-slate-200/60"
                        loading="lazy"
                    >
                </div>
            </div>
        </section>
        @endguest

        @guest
        <section class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 mb-8">
            <div class="signal-card">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 grid place-items-center mb-4">{!! $icon('rocket', 18) !!}</div>
                <h2 class="text-base font-bold text-slate-950 mb-2">Start messy</h2>
                <p class="text-sm text-slate-500 leading-6">One sentence is enough. The engine turns it into usable hooks, rhythm, and structure.</p>
            </div>
            <div class="signal-card">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 grid place-items-center mb-4">{!! $icon('chart', 18) !!}</div>
                <h2 class="text-base font-bold text-slate-950 mb-2">Choose the angle</h2>
                <p class="text-sm text-slate-500 leading-6">Test curiosity, controversy, relatability, story, or value without rewriting from scratch.</p>
            </div>
            <div class="signal-card">
                <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-700 grid place-items-center mb-4">{!! $icon('save', 18) !!}</div>
                <h2 class="text-base font-bold text-slate-950 mb-2">Track the winners</h2>
                <p class="text-sm text-slate-500 leading-6">Save posts, log performance, and learn which formats deserve more of your time.</p>
            </div>
        </section>
        @endguest

        @guest
        <section id="pricing" class="mb-12 fade-in scroll-mt-24">
            <div class="text-center mb-10">
                <h2 class="text-3xl sm:text-4xl font-bold logo-text mb-3 text-slate-950">Simple, usage-aligned pricing</h2>
                <p class="text-gray-600 text-lg">No hidden fees. Cancel anytime.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div class="pricing-card bg-white rounded-2xl border border-gray-200 p-6 sm:p-8">
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Free</div>
                    <div class="text-4xl font-bold text-gray-900 mb-1">$0</div>
                    <div class="text-sm text-gray-500 mb-6">forever</div>
                    <ul class="space-y-3 text-sm text-gray-600 mb-8">
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>20 generations/mo</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>1 Viral Lab/mo</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>7-day history</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Basic drivers (5)</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Star 5 posts max</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Streaks & achievements</span></li>
                    </ul>
                    <a href="{{ auth()->check() ? route('dashboard') : route('auth.google.redirect') }}" class="block w-full text-center bg-gray-100 text-gray-700 font-semibold py-2.5 rounded-lg hover:bg-gray-200 transition">Sign up free</a>
                    <p class="text-xs text-gray-400 text-center mt-2">No credit card required</p>
                </div>

                <div class="pricing-card bg-white rounded-2xl border-2 border-indigo-200 p-6 sm:p-8 relative">
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">MOST POPULAR</span>
                    <div class="text-sm font-bold text-indigo-600 uppercase tracking-wide mb-2">Starter</div>
                    <div class="text-4xl font-bold text-gray-900 mb-1">${{ number_format(config('postsmith.tiers.starter.checkout_monthly_price'), 2) }}</div>
                    <div class="text-sm text-gray-500 mb-1">first month, then ${{ config('postsmith.tiers.starter.monthly_price') }}/month</div>
                    <div class="text-xs text-indigo-600 font-semibold mb-6">50% off first month. Cancel anytime.</div>
                    <ul class="space-y-3 text-sm text-gray-600 mb-8">
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>200 generations/mo</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>5 Viral Lab/mo</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>90-day history</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Full personal forge</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Unlimited starred posts</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>RSS Feed for schedulers (Buffer, SocialBee, etc.)</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Streaks & achievements</span></li>
                        <li class="flex items-start gap-2">{!! $icon('lock',14,'text-gray-300 mt-0.5') !!}<span class="text-gray-400">Direct Publish - Pro only</span></li>
                    </ul>
                    <button type="button" onclick="payPro('starter','monthly')" class="block w-full text-center bg-indigo-600 text-white font-semibold py-2.5 rounded-lg hover:bg-indigo-700 transition">Start for ${{ number_format(config('postsmith.tiers.starter.checkout_monthly_price'), 2) }}</button>
                    <button type="button" onclick="payPro('starter','annual')" class="block w-full text-center mt-2 bg-indigo-50 text-indigo-700 font-semibold py-2.5 rounded-lg hover:bg-indigo-100 transition">Annual first year - ${{ config('postsmith.tiers.starter.checkout_annual_price') }}</button>
                </div>

                <div class="pricing-card bg-white rounded-2xl border border-gray-200 p-6 sm:p-8">
                    <div class="text-sm font-bold text-purple-600 uppercase tracking-wide mb-2">Pro</div>
                    <div class="text-4xl font-bold text-gray-900 mb-1">${{ number_format(config('postsmith.tiers.pro.checkout_monthly_price'), 2) }}</div>
                    <div class="text-sm text-gray-500 mb-1">first month, then ${{ config('postsmith.tiers.pro.monthly_price') }}/month</div>
                    <div class="text-xs text-purple-600 font-semibold mb-6">50% off first month. Cancel anytime.</div>
                    <ul class="space-y-3 text-sm text-gray-600 mb-8">
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>1,000 generations/mo</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Unlimited Viral Lab</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Forever history</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Full forge + global drivers</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Export CSV</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Unlimited starred posts</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Remix any starred post</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>RSS Feed (Buffer, SocialBee, etc.)</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Direct Publish to X, LinkedIn, etc.</span></li>
                        <li class="flex items-start gap-2">{!! $icon('check',14,'text-green-500 mt-0.5') !!}<span>Schedule posts (coming soon)</span></li>
                    </ul>
                    <button type="button" onclick="payPro('pro','monthly')" class="block w-full text-center bg-purple-600 text-white font-semibold py-2.5 rounded-lg hover:bg-purple-700 transition">Start for ${{ number_format(config('postsmith.tiers.pro.checkout_monthly_price'), 2) }}</button>
                    <button type="button" onclick="payPro('pro','annual')" class="block w-full text-center mt-2 bg-purple-50 text-purple-700 font-semibold py-2.5 rounded-lg hover:bg-purple-100 transition">Annual first year - ${{ config('postsmith.tiers.pro.checkout_annual_price') }}</button>
                </div>
            </div>
            <p class="text-center text-xs text-gray-400 mt-6">Annual plans are 20% off for the first year. Secured by Flutterwave.</p>
        </section>
        @endguest

        @guest
        <section class="mb-8">
            <div class="text-center mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600">What creators are saying</p>
                <h2 class="text-2xl sm:text-3xl font-bold logo-text text-slate-950 mt-2">Built for posts people actually answer.</h2>
            </div>
            <div class="comment-rail" aria-label="Creator comments">
                <div class="comment-track">
                    <div class="comment-card">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="avatar-dot">AM</span>
                            <div><p class="text-sm font-bold text-slate-900">Ada M.</p><p class="text-xs text-slate-400">Newsletter creator</p></div>
                        </div>
                        <p class="text-sm text-slate-600 leading-6">"It took my rough thought and gave me a hook I would actually post."</p>
                    </div>
                    <div class="comment-card">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="avatar-dot">CJ</span>
                            <div><p class="text-sm font-bold text-slate-900">CJ O.</p><p class="text-xs text-slate-400">Community builder</p></div>
                        </div>
                        <p class="text-sm text-slate-600 leading-6">"The driver angles make testing posts feel much more intentional."</p>
                    </div>
                    <div class="comment-card">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="avatar-dot">TN</span>
                            <div><p class="text-sm font-bold text-slate-900">Tomi N.</p><p class="text-xs text-slate-400">Founder</p></div>
                        </div>
                        <p class="text-sm text-slate-600 leading-6">"It sounds like me, just clearer and sharper."</p>
                    </div>
                    <div class="comment-card">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="avatar-dot">AM</span>
                            <div><p class="text-sm font-bold text-slate-900">Ada M.</p><p class="text-xs text-slate-400">Newsletter creator</p></div>
                        </div>
                        <p class="text-sm text-slate-600 leading-6">"It took my rough thought and gave me a hook I would actually post."</p>
                    </div>
                    <div class="comment-card">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="avatar-dot">CJ</span>
                            <div><p class="text-sm font-bold text-slate-900">CJ O.</p><p class="text-xs text-slate-400">Community builder</p></div>
                        </div>
                        <p class="text-sm text-slate-600 leading-6">"The driver angles make testing posts feel much more intentional."</p>
                    </div>
                    <div class="comment-card">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="avatar-dot">TN</span>
                            <div><p class="text-sm font-bold text-slate-900">Tomi N.</p><p class="text-xs text-slate-400">Founder</p></div>
                        </div>
                        <p class="text-sm text-slate-600 leading-6">"It sounds like me, just clearer and sharper."</p>
                    </div>
                </div>
            </div>
        </section>
        @endguest

        @if ($items)
            <div id="{{ $generated ? 'results' : 'results-rewrite' }}" class="mb-8 fade-in">
                @if ($rewrites)
                    <div class="analysis-box text-white rounded-2xl p-6 sm:p-8 mb-6">
                        <h3 class="text-lg font-bold mb-4 logo-text flex items-center gap-2">{!! $icon('clipboard',16) !!} PostSmith's Honest Take</h3>
                        <div class="bg-white/10 rounded-lg p-3">
                            <p class="text-yellow-300 font-semibold text-sm mb-1">Direction</p>
                            <p class="text-gray-200 text-sm">Your draft has been restructured with clearer hooks, stronger rhythm, and driver-specific angles.</p>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-xl font-bold logo-text">{{ count($items) }} {{ $generated ? 'post ideas' : 'fixed versions' }}</h3>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-gray-400 hover:text-gray-600 px-3 py-1.5 rounded-md text-xs font-medium border border-transparent hover:border-gray-200 transition">Clear</a>
                </div>

                <div class="space-y-5">
                    @foreach ($items as $i => $item)
                        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover" id="post-card-{{ $i }}">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="driver-badge px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-indigo-50 border-indigo-200 text-indigo-800">{{ $item['driver'] ?? 'Post' }}</span>
                                <span class="text-xs text-gray-400 font-medium ml-auto">#{{ $i + 1 }}</span>
                            </div>
                            @if (!empty($item['what_changed']))
                                <div class="post-changed mb-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                                    <p class="text-sm text-indigo-800"><strong>What we changed:</strong> {{ $item['what_changed'] }}</p>
                                </div>
                            @endif
                            <div class="post-meta mb-4 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                <span class="text-gray-500"><strong>Why this works:</strong> {{ $item['why_it_works'] ?? 'This version gives the thought a clearer emotional angle and a stronger reason to keep reading.' }}</span>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-5 mb-4 border border-gray-100">
                                <div class="post-text text-gray-800 text-base" id="post-{{ $i }}">{{ $item['text'] ?? '' }}</div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="copyPost('post-{{ $i }}', this)" class="copy-btn flex-1 min-w-0 bg-gray-900 text-white py-2.5 rounded-lg font-medium text-sm hover:bg-gray-800 transition flex items-center justify-center gap-1.5">{!! $icon('clipboard',14) !!} Copy</button>
                                @auth
                                    <form method="POST" action="{{ route('posts.store') }}" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="platform" value="{{ $platform }}">
                                        <input type="hidden" name="length" value="{{ $length }}">
                                        <input type="hidden" name="driver" value="{{ $item['driver'] ?? 'Post' }}">
                                        <input type="hidden" name="post_text" value="{{ $item['text'] ?? '' }}">
                                        <input type="hidden" name="raw_thought" value="{{ old('thought', old('draft')) }}">
                                        <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium text-sm hover:bg-indigo-700 transition flex items-center justify-center gap-1.5">{!! $icon('save',14) !!} Save & Track</button>
                                    </form>
                                @else
                                    <a href="{{ route('auth.google.redirect') }}" class="flex-1 bg-gray-100 text-gray-500 py-2.5 rounded-lg font-medium text-sm hover:bg-gray-200 transition flex items-center justify-center gap-1.5">{!! $icon('lock',14) !!} Save & Track</a>
                                @endauth
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div id="tracker" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mt-8">
            <div class="flex items-center justify-between mb-2 flex-wrap gap-4">
                <div><h3 class="text-xl font-bold logo-text flex items-center gap-2">{!! $icon('chart',18) !!} Your Performance</h3></div>
                @if ($stats['best_driver'])
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 px-4 py-2 rounded-lg"><span class="text-sm font-semibold text-indigo-800 flex items-center gap-1">{!! $icon('star',14) !!} Your best format: {{ $stats['best_driver'] }}</span></div>
                @endif
            </div>
            <p class="text-sm text-gray-500 mb-6 max-w-2xl">Log your results after posting. We analyze what works across the community to keep improving PostSmith's engine.</p>

            @auth
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100"><div class="text-2xl font-bold text-gray-900">{{ number_format($stats['posts']) }}</div><div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mt-1">Posts Tracked</div></div>
                    <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100"><div class="text-2xl font-bold text-blue-700">{{ number_format($stats['likes']) }}</div><div class="text-xs text-blue-600 uppercase tracking-wide font-semibold mt-1">Likes</div></div>
                    <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100"><div class="text-2xl font-bold text-green-700">{{ number_format($stats['comments']) }}</div><div class="text-xs text-green-600 uppercase tracking-wide font-semibold mt-1">Comments</div></div>
                    <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-100"><div class="text-2xl font-bold text-purple-700">{{ number_format($stats['shares']) }}</div><div class="text-xs text-purple-600 uppercase tracking-wide font-semibold mt-1">Shares</div></div>
                </div>

                @forelse ($posts as $post)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-3">
                        <div class="flex items-center gap-3 mb-2"><span class="inline-block px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">{{ $post->driver }}</span><span class="text-sm text-gray-500">{{ $post->platform }}</span></div>
                        <p class="post-text text-gray-700">{{ $post->post_text }}</p>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400"><div class="flex justify-center mb-3">{!! $icon('file',40,'text-gray-300') !!}</div><p>No posts tracked yet. Generate one, save it, publish it, then record your results here!</p></div>
                @endforelse
            @else
                <div class="text-center py-12 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
                    <div class="flex justify-center mb-3">{!! $icon('lock',40,'text-gray-300') !!}</div>
                    <p class="mb-2 font-medium text-gray-600">Track which posts actually perform.</p>
                    <p class="text-sm mb-4">Sign in with Google to save posts, log likes & comments, and see which formats work.</p>
                    <a href="{{ route('auth.google.redirect') }}" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold text-sm hover:bg-indigo-700 transition inline-block">Sign in with Google</a>
                </div>
            @endauth
        </div>

        <footer class="mt-10 border-t border-gray-200 pt-8 pb-10">
            <div class="grid grid-cols-1 md:grid-cols-[1.2fr_0.8fr] gap-8 items-start">
                <div>
                    <a href="{{ route('dashboard') }}" class="logo-text text-2xl text-slate-950 inline-flex items-center gap-2.5">
                        <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-10 h-10 object-contain shrink-0">
                        <span>PostSmith</span>
                    </a>
                    <p class="text-sm text-gray-500 max-w-md mt-3 leading-relaxed">Your thought, amplified. Turn raw ideas into posts with stronger hooks, clearer structure, and engagement drivers built for real conversations.</p>
                </div>
                <div class="grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Product</p>
                        <div class="space-y-2 text-gray-500">
                            <a href="#mode-scratch" onclick="switchMode('scratch')" class="block hover:text-indigo-600 transition">Generate</a>
                            <a href="#mode-rewrite" onclick="switchMode('rewrite')" class="block hover:text-indigo-600 transition">Rewrite</a>
                            <a href="#mode-viral_lab" onclick="switchMode('viral_lab')" class="block hover:text-indigo-600 transition">Viral Lab</a>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Legal</p>
                        <div class="space-y-2 text-gray-500">
                            <a href="{{ route('terms') }}" class="block hover:text-indigo-600 transition">Terms</a>
                            <a href="{{ route('privacy') }}" class="block hover:text-indigo-600 transition">Privacy Policy</a>
                            <a href="mailto:{{ config('postsmith.sender_email') }}" class="block hover:text-indigo-600 transition">Contact</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-400">
                <p>&copy; {{ date('Y') }} PostSmith. All rights reserved.</p>
                <p>Built for creators who want replies, not silence.</p>
            </div>
        </footer>
    </div>

    <script>
        var FLW_PUBLIC_KEY = @json(config('postsmith.payments.flutterwave_public_key'));
        var FLW_CURRENCY = @json(config('postsmith.payments.currency'));
        var BILLING_VERIFY_URL = @json(route('billing.flutterwave.verify'));
        var AUTH_REDIRECT_URL = @json(route('auth.google.redirect'));
        var AUTH_USER = @json(auth()->check() ? ['id' => auth()->id(), 'email' => auth()->user()->email, 'name' => auth()->user()->name ?: auth()->user()->email] : null);
        var PLAN_AMOUNTS = {
            starter: { monthly: {{ (float) config('postsmith.tiers.starter.checkout_monthly_price') }}, annual: {{ (float) config('postsmith.tiers.starter.checkout_annual_price') }} },
            pro: { monthly: {{ (float) config('postsmith.tiers.pro.checkout_monthly_price') }}, annual: {{ (float) config('postsmith.tiers.pro.checkout_annual_price') }} }
        };

        function switchMode(mode) {
            ['scratch', 'rewrite', 'viral_lab'].forEach(function(name) {
                var panel = document.getElementById('mode-' + name);
                var tab = document.getElementById('tab-' + name);
                if (!panel || !tab) return;
                if (name === mode) {
                    panel.classList.remove('hidden');
                    tab.classList.add('active');
                } else {
                    panel.classList.add('hidden');
                    tab.classList.remove('active');
                }
            });
        }

        function toggleAdvanced(id) {
            var el = document.getElementById(id);
            var icon = document.getElementById(id + '-icon');
            if (!el) return;
            el.classList.toggle('hidden');
            if (icon) icon.textContent = el.classList.contains('hidden') ? '+' : '-';
        }

        function payPro(tier, plan) {
            if (!AUTH_USER) {
                window.location.href = AUTH_REDIRECT_URL;
                return;
            }
            if (!FLW_PUBLIC_KEY || typeof FlutterwaveCheckout !== 'function') {
                alert('Flutterwave is not configured yet. Add FLW_PUBLIC_KEY and FLW_SECRET_KEY in .env.');
                return;
            }

            var amount = PLAN_AMOUNTS[tier] && PLAN_AMOUNTS[tier][plan] ? PLAN_AMOUNTS[tier][plan] : PLAN_AMOUNTS.pro.monthly;
            var txRef = 'POSTSMITH_' + AUTH_USER.id + '_' + tier + '_' + plan + '_' + Date.now();

            FlutterwaveCheckout({
                public_key: FLW_PUBLIC_KEY,
                tx_ref: txRef,
                amount: amount,
                currency: FLW_CURRENCY,
                payment_options: 'card,banktransfer,ussd',
                customer: { email: AUTH_USER.email, name: AUTH_USER.name },
                callback: function(data) {
                    var params = new URLSearchParams({
                        tx_ref: data.tx_ref,
                        transaction_id: data.transaction_id,
                        plan: plan,
                        tier: tier
                    });
                    window.location.href = BILLING_VERIFY_URL + '?' + params.toString();
                },
                onclose: function() {},
                customizations: {
                    title: 'PostSmith ' + tier.charAt(0).toUpperCase() + tier.slice(1),
                    description: plan === 'annual' ? 'Annual subscription' : 'Monthly subscription',
                    logo: ''
                }
            });
        }

        function updateForgeSelection(containerId, countId) {
            var container = document.getElementById(containerId);
            if (!container) return;
            var checked = container.querySelectorAll('input[type="checkbox"]:checked');
            var all = container.querySelectorAll('input[type="checkbox"]');
            if (checked.length > 5) {
                checked[checked.length - 1].checked = false;
                checked = container.querySelectorAll('input[type="checkbox"]:checked');
            }
            all.forEach(function(input) {
                var span = input.nextElementSibling;
                if (!span) return;
                if (input.checked) {
                    span.className = 'inline-block px-3 py-1.5 rounded-full text-xs font-semibold border transition bg-indigo-600 text-white border-indigo-600';
                } else {
                    span.className = 'inline-block px-3 py-1.5 rounded-full text-xs font-semibold border transition bg-white text-gray-700 border-gray-300 hover:border-indigo-400';
                }
            });
            var count = document.getElementById(countId);
            if (count) count.textContent = checked.length + ' of 5 selected';
        }

        function copyPost(elementId, btn) {
            var el = document.getElementById(elementId);
            if (!el) return;
            var text = el.innerText || el.textContent;
            if (!navigator.clipboard) return;
            navigator.clipboard.writeText(text).then(function() {
                var original = btn.innerHTML;
                btn.innerHTML = '{!! $icon('check',14) !!} Copied!';
                btn.classList.remove('bg-gray-900');
                btn.classList.add('bg-green-600');
                setTimeout(function() {
                    btn.innerHTML = original;
                    btn.classList.remove('bg-green-600');
                    btn.classList.add('bg-gray-900');
                }, 2000);
            });
        }

        document.querySelectorAll('#form-generate, #form-rewrite').forEach(function(form) {
            form.addEventListener('submit', function() {
                var overlay = document.getElementById('loading-overlay');
                if (overlay) overlay.classList.add('active');
            });
        });
    </script>
</body>
</html>
