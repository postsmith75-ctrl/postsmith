<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PostSmith</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #f8fafc; }
        .logo-text { font-weight: 700; letter-spacing: -0.03em; }
        .step-panel { display: none; }
        .step-panel.active { display: block; }
        .option-card input:checked + span {
            border-color: #4f46e5;
            background: #eef2ff;
            color: #3730a3;
        }
    </style>
</head>
<body class="min-h-screen text-slate-900">
    <main class="min-h-screen px-5 py-8 sm:px-8 flex items-center justify-center">
        <section class="w-full max-w-xl">
            <a href="{{ route('dashboard') }}" class="logo-text text-2xl inline-flex items-center gap-2.5 mb-8">
                <img src="{{ asset('postsmith-logo-mark.png') }}" alt="PostSmith logo" class="w-10 h-10 object-contain">
                <span>PostSmith</span>
            </a>

            <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/60">
                @if (session('onboarding_complete'))
                    <div class="space-y-6">
                        <div>
                            <p class="text-sm font-bold text-indigo-600 uppercase tracking-wide">Ready</p>
                            <h1 class="logo-text text-3xl sm:text-4xl text-slate-950 mt-2">You're all set! 🎉</h1>
                            <p class="text-slate-600 text-lg leading-8 mt-4">Your workspace is ready.</p>
                        </div>

                        <a href="{{ route('dashboard') }}" class="inline-flex w-full sm:w-auto justify-center bg-indigo-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
                            Go to Dashboard
                        </a>
                    </div>
                @else
                    @if ($errors->any())
                        <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm font-semibold">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('onboarding.store') }}" id="onboarding-form">
                        @csrf

                        <div class="step-panel active" data-step="1">
                            <p class="text-sm font-bold text-indigo-600 uppercase tracking-wide">Welcome</p>
                            <h1 class="logo-text text-3xl sm:text-4xl text-slate-950 mt-2">Welcome to Postsmith 👋</h1>
                            <p class="text-slate-600 text-lg leading-8 mt-4">Let's personalize your experience.<br>This will only take about 20 seconds.</p>

                            <button type="button" data-next class="mt-8 w-full sm:w-auto bg-indigo-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
                                Continue
                            </button>
                        </div>

                        <div class="step-panel" data-step="2">
                            <p class="text-sm font-bold text-indigo-600 uppercase tracking-wide">Step 2 of 3</p>
                            <h1 class="logo-text text-2xl sm:text-3xl text-slate-950 mt-2">What are you using Postsmith for?</h1>

                            <div class="grid sm:grid-cols-2 gap-3 mt-6">
                                @foreach ($useCases as $useCase)
                                    <label class="option-card cursor-pointer">
                                        <input type="radio" name="use_case" value="{{ $useCase }}" class="sr-only" @checked(old('use_case') === $useCase)>
                                        <span class="block border border-slate-200 rounded-lg px-4 py-3 font-bold text-slate-700 hover:border-indigo-300 hover:bg-slate-50 transition">{{ $useCase }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row gap-3 mt-8">
                                <button type="button" data-prev class="w-full sm:w-auto border border-slate-300 text-slate-700 font-bold px-6 py-3 rounded-lg hover:bg-slate-50 transition">Back</button>
                                <button type="button" data-next class="w-full sm:w-auto bg-indigo-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-indigo-700 transition">Continue</button>
                            </div>
                        </div>

                        <div class="step-panel" data-step="3">
                            <p class="text-sm font-bold text-indigo-600 uppercase tracking-wide">Step 3 of 3</p>
                            <h1 class="logo-text text-2xl sm:text-3xl text-slate-950 mt-2">Tell us about yourself or your brand</h1>

                            <div class="mt-6">
                                <textarea name="brand_context" rows="6" maxlength="1000" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition" placeholder="&quot;I'm a software developer sharing AI tips.&quot;&#10;&#10;&quot;I own a fashion business.&quot;&#10;&#10;&quot;I manage my church's social media.&quot;">{{ old('brand_context') }}</textarea>
                                <p class="text-sm text-slate-500 mt-2">Optional. This helps Postsmith generate more relevant content.</p>
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row gap-3 mt-8">
                                <button type="button" data-prev class="w-full sm:w-auto border border-slate-300 text-slate-700 font-bold px-6 py-3 rounded-lg hover:bg-slate-50 transition">Back</button>
                                <button type="submit" class="w-full sm:w-auto bg-indigo-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-indigo-700 transition">Continue</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </main>

    <script>
        const panels = Array.from(document.querySelectorAll('.step-panel'));
        let activeStep = 1;

        function showStep(step) {
            activeStep = step;
            panels.forEach((panel) => panel.classList.toggle('active', Number(panel.dataset.step) === activeStep));
        }

        document.querySelectorAll('[data-next]').forEach((button) => {
            button.addEventListener('click', () => {
                if (activeStep === 2 && !document.querySelector('input[name="use_case"]:checked')) {
                    document.querySelector('input[name="use_case"]').setCustomValidity('Please choose an option.');
                    document.querySelector('input[name="use_case"]').reportValidity();
                    return;
                }

                document.querySelectorAll('input[name="use_case"]').forEach((input) => input.setCustomValidity(''));
                showStep(Math.min(activeStep + 1, 3));
            });
        });

        document.querySelectorAll('[data-prev]').forEach((button) => {
            button.addEventListener('click', () => showStep(Math.max(activeStep - 1, 1)));
        });

        if (document.querySelector('.text-red-800')) {
            showStep(2);
        }
    </script>
</body>
</html>
