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
        ]);

        $generatorPreferences->remember($request->user(), $data);

        if ($request->wantsJson()) {
            return response()->json([
                'platform' => $data['platform'],
                'goal' => $data['goal'],
            ]);
        }

        return back()->with('status', 'Content strategy updated.');
    }
}
