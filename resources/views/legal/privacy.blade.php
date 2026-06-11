<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - PostSmith</title>
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
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="logo-text text-xl text-slate-950 inline-flex items-center gap-2.5">
                <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-9 h-9 object-contain shrink-0">
                <span>PostSmith</span>
            </a>
            <a href="{{ route('dashboard') }}" class="text-sm font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-2 rounded-lg">Back to app</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <article class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-8 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600 mb-3">Legal</p>
            <h1 class="logo-text text-3xl sm:text-4xl text-slate-950 mb-2">Privacy Policy</h1>
            <div class="mt-8 whitespace-pre-line text-slate-600 leading-8 text-sm sm:text-base">
                {{ trim(file_get_contents(resource_path('legal/privacy.txt'))) }}
            </div>
        </article>
    </main>
</body>
</html>
