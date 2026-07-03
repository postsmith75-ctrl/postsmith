<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Services\Postsmith\AiMemory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    private const USE_CASES = [
        'Personal Use',
        'Business',
        'Content Creation',
        'Marketing',
        'School',
        'Just Exploring',
    ];

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->onboarding_completed_at && ! session('onboarding_complete')) {
            return redirect()->route('dashboard');
        }

        return view('postsmith.onboarding', [
            'useCases' => self::USE_CASES,
        ]);
    }

    public function store(Request $request, AiMemory $memory): RedirectResponse
    {
        $user = $request->user();

        if ($user->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'use_case' => ['required', 'string', Rule::in(self::USE_CASES)],
            'brand_context' => ['nullable', 'string', 'max:1000'],
        ]);

        $memory->saveOnboardingMemory($user, $data);

        $user->forceFill([
            'onboarding_completed_at' => now(),
        ])->save();

        return redirect()->route('onboarding.show')->with('onboarding_complete', true);
    }
}
