<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Models\DiscoveredDriver;
use App\Models\Post;
use App\Services\Postsmith\UsageManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, UsageManager $usageManager): View
    {
        $user = $request->user();
        $posts = $user
            ? $user->posts()->latest()->limit(10)->get()
            : collect();
        $allPosts = $user
            ? $user->posts()->get()
            : collect();

        $stats = [
            'posts' => $allPosts->count(),
            'likes' => $allPosts->sum('likes'),
            'comments' => $allPosts->sum('comments'),
            'shares' => $allPosts->sum('shares'),
            'best_driver' => $allPosts
                ->groupBy('driver')
                ->map(fn ($items) => $items->sum(fn (Post $post) => $post->engagementScore()))
                ->sortDesc()
                ->keys()
                ->first(),
            'best_platform' => $allPosts
                ->groupBy('platform')
                ->map(fn ($items) => $items->sum(fn (Post $post) => $post->engagementScore()))
                ->sortDesc()
                ->keys()
                ->first(),
            'best_length' => $allPosts
                ->groupBy('length')
                ->map(fn ($items) => $items->sum(fn (Post $post) => $post->engagementScore()))
                ->sortDesc()
                ->keys()
                ->first(),
        ];

        return view('postsmith.dashboard', [
            'brand' => config('postsmith.brand'),
            'drivers' => config('postsmith.drivers'),
            'driverLibrary' => DiscoveredDriver::query()->where('status', 'active')->orderBy('driver_name')->get(),
            'platforms' => ['Facebook Groups/Feed', 'LinkedIn', 'Twitter/X', 'Instagram', 'Threads', 'Reddit'],
            'lengths' => ['short' => 'Short', 'medium' => 'Medium', 'long' => 'Long'],
            'generated' => session('generated'),
            'rewrites' => session('rewrites'),
            'source' => session('source'),
            'posts' => $posts,
            'stats' => $stats,
            'usage' => $usageManager->status($user),
            'recentPayments' => $user ? $user->payments()->where('status', 'paid')->latest('paid_at')->limit(5)->get() : collect(),
        ]);
    }
}
