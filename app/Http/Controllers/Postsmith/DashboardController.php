<?php

namespace App\Http\Controllers\Postsmith;

use App\Http\Controllers\Controller;
use App\Models\DiscoveredDriver;
use App\Models\Post;
use App\Services\Postsmith\UsageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(Request $request, UsageManager $usageManager): View|RedirectResponse
    {
        $user = $request->user();

        if ($user && ! $user->onboarding_completed_at) {
            return redirect()->route('onboarding.show');
        }

        $historyDays = $usageManager->historyDays($user);
        $postsQuery = $user ? $user->posts()->latest() : null;

        if ($postsQuery && $historyDays > 0) {
            $postsQuery->where('created_at', '>=', now()->subDays($historyDays));
        }

        if ($user && $usageManager->canUseRss($user) && ! $user->z_rss_token) {
            $user->forceFill(['z_rss_token' => Str::random(48)])->save();
        }

        $posts = $postsQuery ? (clone $postsQuery)->limit(10)->get() : collect();
        $allPosts = $postsQuery ? (clone $postsQuery)->get() : collect();
        $drivers = collect(config('postsmith.drivers'));

        if ($user && ($user->isPro() || $user->isAdmin())) {
            $drivers = $drivers
                ->merge(DiscoveredDriver::query()->where('status', 'active')->orderBy('driver_name')->pluck('driver_name'))
                ->unique()
                ->values();
        }

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
            'drivers' => $drivers->all(),
            'driverLibrary' => DiscoveredDriver::query()->where('status', 'active')->orderBy('driver_name')->get(),
            'platforms' => ['Facebook Groups/Feed', 'LinkedIn', 'Twitter/X', 'Instagram', 'Threads', 'Reddit'],
            'lengths' => ['short' => 'Short', 'medium' => 'Medium', 'long' => 'Long'],
            'generated' => session('generated'),
            'rewrites' => session('rewrites'),
            'source' => session('source'),
            'posts' => $posts,
            'stats' => $stats,
            'usage' => $usageManager->status($user),
            'viralUsage' => $user ? $usageManager->viralLabStatus($user) : null,
            'historyDays' => $historyDays,
            'starLimit' => $user ? $usageManager->starLimit($user) : 5,
            'canUseRss' => $user ? $usageManager->canUseRss($user) : false,
            'canExportCsv' => $user ? $usageManager->canExportCsv($user) : false,
            'recentPayments' => $user ? $user->payments()->where('status', 'paid')->latest('paid_at')->limit(5)->get() : collect(),
        ]);
    }
}
