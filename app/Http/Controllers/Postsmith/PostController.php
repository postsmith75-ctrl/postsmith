<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user(), 403);

        $data = $request->validate([
            'platform' => ['required', 'string', 'max:100'],
            'length' => ['required', 'string', 'max:50'],
            'driver' => ['required', 'string', 'max:100'],
            'post_text' => ['required', 'string'],
            'raw_thought' => ['nullable', 'string'],
        ]);

        $request->user()->posts()->create($data);

        return back()->with('status', 'Post saved to your tracker.');
    }

    public function updateMetrics(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user() && $post->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'likes' => ['required', 'integer', 'min:0'],
            'comments' => ['required', 'integer', 'min:0'],
            'shares' => ['required', 'integer', 'min:0'],
        ]);

        $post->update($data);

        return back()->with('status', 'Metrics updated.');
    }

    public function toggleStar(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user() && $post->user_id === $request->user()->id, 403);

        $post->update([
            'is_starred' => ! $post->is_starred,
            'starred_at' => $post->is_starred ? null : now(),
        ]);

        return back()->with('status', $post->is_starred ? 'Post starred.' : 'Post unstarred.');
    }
}
