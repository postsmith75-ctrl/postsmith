<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Models\ViralLabSubmission;
use App\Services\Postsmith\ContentGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ViralLabController extends Controller
{
    public function store(Request $request, ContentGenerator $generator): RedirectResponse
    {
        abort_unless($request->user(), 403);

        $data = $request->validate([
            'post_text' => ['required', 'string', 'min:80'],
            'platform' => ['required', 'string', 'max:100'],
            'likes' => ['required', 'integer', 'min:0'],
            'comments' => ['required', 'integer', 'min:0'],
            'shares' => ['required', 'integer', 'min:0'],
        ]);

        $rules = config('postsmith.viral_lab');
        $wordCount = str_word_count(strip_tags($data['post_text']));

        if (
            $wordCount < $rules['min_words']
            || $data['likes'] < $rules['min_likes']
            || $data['comments'] < $rules['min_comments']
            || $data['shares'] < $rules['min_shares']
        ) {
            return back()->withErrors([
                'post_text' => "Viral Lab minimums are {$rules['min_words']} words, {$rules['min_likes']} likes, {$rules['min_comments']} comments, and {$rules['min_shares']} shares.",
            ])->withInput();
        }

        $result = $generator->analyzeViralPost($data['post_text'], $data['platform']);
        $analysis = $result['analysis'];

        ViralLabSubmission::create([
            ...$data,
            'user_id' => $request->user()->id,
            'word_count' => $wordCount,
            'detected_drivers' => $analysis['detected_drivers'] ?? [],
            'new_driver_flag' => (bool) ($analysis['new_driver_detected'] ?? false),
            'new_driver_name' => $analysis['new_driver_name'] ?? null,
            'ai_analysis' => $analysis,
            'status' => 'reviewed',
        ]);

        return back()
            ->with('viral_analysis', $analysis)
            ->with('source', $result['source']);
    }
}
