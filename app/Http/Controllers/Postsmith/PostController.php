<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Services\Postsmith\UsageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function toggleStar(Request $request, Post $post, UsageManager $usageManager): RedirectResponse
    {
        abort_unless($request->user() && $post->user_id === $request->user()->id, 403);

        if (! $post->is_starred && ! $usageManager->canStarAnotherPost($request->user())) {
            return back()->withErrors(['post' => 'Free users can star up to 5 posts. Upgrade for unlimited starred posts.']);
        }

        $post->update([
            'is_starred' => ! $post->is_starred,
            'starred_at' => $post->is_starred ? null : now(),
        ]);

        return back()->with('status', $post->is_starred ? 'Post starred.' : 'Post unstarred.');
    }

    public function export(Request $request, UsageManager $usageManager): StreamedResponse|RedirectResponse
    {
        abort_unless($request->user(), 403);

        if (! $usageManager->canExportCsv($request->user())) {
            return back()->withErrors(['export' => 'CSV export is available on Pro.']);
        }

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Platform', 'Length', 'Driver', 'Post', 'Likes', 'Comments', 'Shares', 'Starred', 'Created']);

            $request->user()->posts()->latest()->chunk(200, function ($posts) use ($out) {
                foreach ($posts as $post) {
                    fputcsv($out, [
                        $post->id,
                        $post->platform,
                        $post->length,
                        $post->driver,
                        $post->post_text,
                        $post->likes,
                        $post->comments,
                        $post->shares,
                        $post->is_starred ? 'Yes' : 'No',
                        optional($post->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($out);
        }, 'postsmith_posts_'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function rss(string $token): \Illuminate\Http\Response
    {
        $user = User::where('z_rss_token', $token)->firstOrFail();
        abort_unless($user->isStarter() || $user->isPro() || $user->isAdmin(), 403);

        $posts = $user->posts()->latest()->limit(50)->get();

        return response()
            ->view('feeds.posts', ['user' => $user, 'posts' => $posts])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
