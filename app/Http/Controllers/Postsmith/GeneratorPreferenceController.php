<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Services\Postsmith\GeneratorPreferences;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GeneratorPreferenceController extends Controller
{
    public function update(Request $request, GeneratorPreferences $generatorPreferences): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'platform' => ['required', 'string', 'max:100', Rule::in(config('postsmith.generator.platforms'))],
            'goal' => ['required', 'string', 'max:100', Rule::in(config('postsmith.generator.goals'))],
            'length' => ['sometimes', 'required', 'string', 'max:100', Rule::in(array_keys(config('postsmith.generator.lengths')))],
        ]);

        $lengthKey = $data['length'] ?? $request->user()->generatorPreference?->last_length ?? config('postsmith.generator.defaults.length');
        $data['length'] = $lengthKey;

        $generatorPreferences->remember($request->user(), $data);

        if ($request->wantsJson()) {
            return response()->json([
                'platform' => $data['platform'],
                'goal' => $data['goal'],
                'length_key' => $lengthKey,
                'length_label' => config('postsmith.generator.lengths')[$lengthKey]['label'] ?? $lengthKey,
            ]);
        }

        return back()->with('status', 'Content strategy updated.');
    }
}
