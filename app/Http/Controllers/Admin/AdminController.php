<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscoveredDriver;
use App\Models\GenerationHistory;
use App\Models\Post;
use App\Models\User;
use App\Models\ViralLabSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'pro_users' => User::where('tier', 'pro')->count(),
                'starter_users' => User::where('tier', 'starter')->count(),
                'generations' => GenerationHistory::count(),
                'tracked_posts' => Post::count(),
                'viral_submissions' => ViralLabSubmission::count(),
                'active_drivers' => DiscoveredDriver::where('status', 'active')->count(),
                'pending_drivers' => DiscoveredDriver::where('status', 'pending')->count(),
            ],
            'recentUsers' => User::latest()->limit(8)->get(),
            'recentSubmissions' => ViralLabSubmission::with('user')->latest()->limit(8)->get(),
            'topDrivers' => Post::query()
                ->selectRaw('driver, count(*) as posts_count, sum(likes + (comments * 2) + (shares * 3)) as score')
                ->whereNotNull('driver')
                ->groupBy('driver')
                ->orderByDesc('score')
                ->limit(8)
                ->get(),
        ]);
    }

    public function users(): View
    {
        return view('admin.users', [
            'users' => User::query()
                ->withCount(['posts', 'generationHistory'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'tier' => ['required', 'in:free,starter,pro'],
            'role' => ['required', 'in:user,admin,owner'],
            'expires_in' => ['nullable', 'in:none,month,year'],
        ]);

        $expiresAt = match ($data['expires_in'] ?? 'none') {
            'month' => now()->addMonth(),
            'year' => now()->addYear(),
            default => $data['tier'] === 'free' ? null : $user->pro_expires_at,
        };

        $user->forceFill([
            'tier' => $data['tier'],
            'role' => $data['role'],
            'pro_expires_at' => $expiresAt,
            'generations_used' => $data['tier'] === 'free' ? $user->generations_used : 0,
        ])->save();

        return back()->with('status', 'User updated.');
    }

    public function drivers(): View
    {
        return view('admin.drivers', [
            'drivers' => DiscoveredDriver::query()
                ->orderByRaw("case status when 'pending' then 0 when 'active' then 1 else 2 end")
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }

    public function updateDriver(Request $request, DiscoveredDriver $driver): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,active,rejected'],
        ]);

        $driver->forceFill([
            'status' => $data['status'],
            'promoted_at' => $data['status'] === 'active' ? ($driver->promoted_at ?: now()) : $driver->promoted_at,
            'new_until' => $data['status'] === 'active' ? ($driver->new_until ?: now()->addDays(14)) : $driver->new_until,
        ])->save();

        return back()->with('status', 'Driver updated.');
    }

    public function viralLab(): View
    {
        return view('admin.viral-lab', [
            'submissions' => ViralLabSubmission::query()
                ->with('user')
                ->latest()
                ->paginate(20),
        ]);
    }
}
