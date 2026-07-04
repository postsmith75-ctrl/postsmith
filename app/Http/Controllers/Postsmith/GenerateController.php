<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Models\GenerationHistory;
use App\Services\Postsmith\ContentGenerator;
use App\Services\Postsmith\GeneratorPreferences;
use App\Services\Postsmith\UsageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GenerateController extends Controller
{
    public function store(Request $request, ContentGenerator $generator, UsageManager $usageManager, GeneratorPreferences $generatorPreferences): RedirectResponse
    {
        $data = $request->validate([
            'thought' => ['required', 'string', 'max:5000'],
            'platform' => ['required', 'string', 'max:100', Rule::in(config('postsmith.generator.platforms'))],
            'goal' => ['nullable', 'string', 'max:100', Rule::in(config('postsmith.generator.goals'))],
            'length' => ['required', 'string', 'max:100', Rule::in(array_keys(config('postsmith.generator.lengths')))],
            'drivers' => ['nullable', 'array', 'max:5'],
            'drivers.*' => ['string', 'max:100'],
        ]);

        if (! $usageManager->canGenerate($request->user())) {
            return back()->withErrors(['thought' => 'Generation limit reached for this plan.'])->withInput();
        }

        if ($request->user() && ! $request->user()->onboarding_completed_at) {
            return redirect()->route('onboarding.show');
        }

        $result = $generator->fromThought($data['thought'], $data['platform'], $data['length'], $data['drivers'] ?? [], $request->user());
        $usageManager->increment($request->user());

        if ($request->user()) {
            $preferenceSettings = [
                'platform' => $data['platform'],
            ];

            if (array_key_exists('goal', $data)) {
                $preferenceSettings['goal'] = $data['goal'];
            }

            if (array_key_exists('length', $data)) {
                $preferenceSettings['length'] = $data['length'];
            }

            $generatorPreferences->remember($request->user(), $preferenceSettings);

            GenerationHistory::create([
                'user_id' => $request->user()->id,
                'mode' => 'scratch',
                'input_text' => $data['thought'],
                'platform' => $data['platform'],
                'length' => $data['length'],
                'generated_json' => ['posts' => $result['posts']],
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->withInput($data)
            ->with('active_tab', 'scratch')
            ->with('generated', $result['posts'])
            ->with('source', $result['source']);
    }
}
