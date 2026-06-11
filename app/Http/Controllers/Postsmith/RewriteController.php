<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Models\GenerationHistory;
use App\Services\Postsmith\ContentGenerator;
use App\Services\Postsmith\UsageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RewriteController extends Controller
{
    public function store(Request $request, ContentGenerator $generator, UsageManager $usageManager): RedirectResponse
    {
        $data = $request->validate([
            'draft' => ['required', 'string', 'max:5000'],
            'platform' => ['required', 'string', 'max:100'],
            'length' => ['required', 'in:short,medium,long'],
            'drivers' => ['nullable', 'array', 'max:5'],
            'drivers.*' => ['string', 'max:100'],
        ]);

        if (! $usageManager->canGenerate($request->user())) {
            return back()->withErrors(['draft' => 'Generation limit reached for this plan.'])->withInput();
        }

        $result = $generator->rewrite($data['draft'], $data['platform'], $data['length'], $data['drivers'] ?? []);
        $usageManager->increment($request->user());

        if ($request->user()) {
            GenerationHistory::create([
                'user_id' => $request->user()->id,
                'mode' => 'rewrite',
                'input_text' => $data['draft'],
                'platform' => $data['platform'],
                'length' => $data['length'],
                'generated_json' => ['rewrites' => $result['rewrites']],
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->withInput($data)
            ->with('rewrites', $result['rewrites'])
            ->with('source', $result['source']);
    }
}
