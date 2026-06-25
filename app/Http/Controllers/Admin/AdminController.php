<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscoveredDriver;
use App\Models\GenerationHistory;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;
use App\Models\ViralLabSubmission;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        [$range, $customStart, $customEnd, $startDate, $endDate] = $this->resolveDateRange($request);

        $totalUsers = User::count();
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $activeUsers = GenerationHistory::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');
        $payingUsers = User::whereIn('tier', ['starter', 'pro'])
            ->where(fn ($query) => $query->whereNull('pro_expires_at')->orWhere('pro_expires_at', '>', now()))
            ->count();
        $freeUsers = max(0, $totalUsers - $payingUsers);
        $conversionRate = $totalUsers > 0 ? round(($payingUsers / $totalUsers) * 100, 2) : 0;
        $totalGenerations = GenerationHistory::whereBetween('created_at', [$startDate, $endDate])->count();
        $genPerUser = $activeUsers > 0 ? round($totalGenerations / $activeUsers, 1) : 0;
        $viralSubmissions = ViralLabSubmission::whereBetween('created_at', [$startDate, $endDate])->count();
        $postsSaved = Post::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalRatings = DB::table('ratings')->count();
        $avgRating = $totalRatings > 0 ? round((float) DB::table('ratings')->avg('rating'), 1) : 0;
        $ratingDist = DB::table('ratings')
            ->select('rating', DB::raw('count(*) as cnt'))
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();
        $rssUsers = User::whereNotNull('z_rss_token')->count();
        $zernioUsers = User::whereNotNull('zernio_api_key')->where('zernio_api_key', '!=', '')->count();

        $starterUsers = User::where('tier', 'starter')
            ->where(fn ($query) => $query->whereNull('pro_expires_at')->orWhere('pro_expires_at', '>', now()))
            ->count();
        $proUsers = User::where('tier', 'pro')
            ->where(fn ($query) => $query->whereNull('pro_expires_at')->orWhere('pro_expires_at', '>', now()))
            ->count();
        $mrr = ($starterUsers * 9) + ($proUsers * 15);
        $arr = $mrr * 12;
        $totalRevenue = (float) Payment::where('status', 'paid')->sum('amount');
        $rangeRevenue = (float) Payment::where('status', 'paid')->whereBetween('paid_at', [$startDate, $endDate])->sum('amount');
        $todayRevenue = (float) Payment::where('status', 'paid')->whereBetween('paid_at', [now()->startOfDay(), now()->endOfDay()])->sum('amount');
        $paidTransactions = Payment::where('status', 'paid')->whereBetween('paid_at', [$startDate, $endDate])->count();
        $churned = User::where('tier', 'free')->whereBetween('downgraded_at', [$startDate, $endDate])->count();
        $churnRate = ($payingUsers + $churned) > 0 ? round(($churned / ($payingUsers + $churned)) * 100, 2) : 0;

        $lastWeekSignups = User::where('created_at', '>=', now()->subDays(14))
            ->where('created_at', '<', now()->subDays(7))
            ->count();
        $thisWeekSignups = User::where('created_at', '>=', now()->subDays(7))->count();
        $wowGrowth = $lastWeekSignups > 0 ? round((($thisWeekSignups - $lastWeekSignups) / $lastWeekSignups) * 100, 1) : ($thisWeekSignups > 0 ? 100 : 0);
        $lastMonthSignups = User::where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))
            ->count();
        $thisMonthSignups = User::where('created_at', '>=', now()->subDays(30))->count();
        $momGrowth = $lastMonthSignups > 0 ? round((($thisMonthSignups - $lastMonthSignups) / $lastMonthSignups) * 100, 1) : ($thisMonthSignups > 0 ? 100 : 0);

        $signupSeries = User::query()
            ->selectRaw('DATE(created_at) as day, count(*) as cnt')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('cnt', 'day');
        $genSeries = GenerationHistory::query()
            ->selectRaw('DATE(created_at) as day, count(*) as cnt')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('cnt', 'day');
        $revenueSeries = Payment::query()
            ->selectRaw('DATE(paid_at) as day, sum(amount) as total')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $chartLabels = [];
        $chartSignups = [];
        $chartGens = [];
        $chartRevenue = [];
        foreach (CarbonPeriod::create($startDate->copy()->startOfDay(), '1 day', $endDate->copy()->startOfDay()) as $date) {
            $day = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M j');
            $chartSignups[] = (int) ($signupSeries[$day] ?? 0);
            $chartGens[] = (int) ($genSeries[$day] ?? 0);
            $chartRevenue[] = round((float) ($revenueSeries[$day] ?? 0), 2);
        }

        $tierDist = User::query()
            ->select('tier', DB::raw('count(*) as cnt'))
            ->groupBy('tier')
            ->orderByDesc('cnt')
            ->get();
        $platformDist = GenerationHistory::query()
            ->select('platform', DB::raw('count(*) as cnt'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->orderByDesc('cnt')
            ->limit(6)
            ->get();
        $ratingValues = collect(range(1, 5))->map(fn ($rating) => (int) optional($ratingDist->firstWhere('rating', $rating))->cnt)->all();

        $recentUsers = User::latest()->limit(8)->get();
        $recentSubmissions = ViralLabSubmission::with('user')->latest()->limit(8)->get();
        $topDrivers = Post::query()
            ->selectRaw('driver, count(*) as posts_count, sum(likes + comments + shares) as engagement')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('driver')
            ->groupBy('driver')
            ->orderByDesc('posts_count')
            ->limit(8)
            ->get();
        $cohorts = $this->buildCohorts();
        $recentSignups = User::whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->limit(20)
            ->get(['id', 'name', 'email', 'tier', 'google_id', 'created_at']);
        $recentActivity = GenerationHistory::with('user')
            ->latest()
            ->limit(15)
            ->get();
        $topUsers = User::query()
            ->leftJoin('generation_history as g', function ($join) use ($startDate, $endDate) {
                $join->on('g.user_id', '=', 'users.id')
                    ->whereBetween('g.created_at', [$startDate, $endDate]);
            })
            ->select('users.id', 'users.name', 'users.email', 'users.tier', DB::raw('count(g.id) as gens'))
            ->groupBy('users.id', 'users.name', 'users.email', 'users.tier')
            ->orderByDesc('gens')
            ->limit(10)
            ->get();
        $recentRatings = DB::table('ratings as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->select('r.rating', 'r.feedback', 'r.created_at', 'u.name', 'u.email')
            ->orderByDesc('r.created_at')
            ->limit(10)
            ->get();
        $recentPayments = Payment::query()
            ->with('user')
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(12)
            ->get();
        $dailyRevenue = Payment::query()
            ->selectRaw('DATE(paid_at) as day, count(*) as payments_count, sum(amount) as total')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(14)
            ->get();

        return view('admin.dashboard', [
            'range' => $range,
            'customStart' => $customStart,
            'customEnd' => $customEnd,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => [
                'users' => $totalUsers,
                'pro_users' => $proUsers,
                'starter_users' => $starterUsers,
                'generations' => $totalGenerations,
                'tracked_posts' => $postsSaved,
                'viral_submissions' => $viralSubmissions,
                'active_drivers' => DiscoveredDriver::where('status', 'active')->count(),
                'pending_drivers' => DiscoveredDriver::where('status', 'pending')->count(),
            ],
            'totalUsers' => $totalUsers,
            'newUsers' => $newUsers,
            'activeUsers' => $activeUsers,
            'payingUsers' => $payingUsers,
            'freeUsers' => $freeUsers,
            'conversionRate' => $conversionRate,
            'totalGenerations' => $totalGenerations,
            'genPerUser' => $genPerUser,
            'viralSubmissions' => $viralSubmissions,
            'postsSaved' => $postsSaved,
            'totalRatings' => $totalRatings,
            'avgRating' => $avgRating,
            'rssUsers' => $rssUsers,
            'zernioUsers' => $zernioUsers,
            'mrr' => $mrr,
            'arr' => $arr,
            'totalRevenue' => $totalRevenue,
            'rangeRevenue' => $rangeRevenue,
            'todayRevenue' => $todayRevenue,
            'paidTransactions' => $paidTransactions,
            'churned' => $churned,
            'churnRate' => $churnRate,
            'wowGrowth' => $wowGrowth,
            'momGrowth' => $momGrowth,
            'chartLabels' => $chartLabels,
            'chartSignups' => $chartSignups,
            'chartGens' => $chartGens,
            'chartRevenue' => $chartRevenue,
            'tierLabels' => $tierDist->map(fn ($tier) => ucfirst((string) $tier->tier))->all(),
            'tierValues' => $tierDist->pluck('cnt')->map(fn ($count) => (int) $count)->all(),
            'platLabels' => $platformDist->pluck('platform')->all(),
            'platValues' => $platformDist->pluck('cnt')->map(fn ($count) => (int) $count)->all(),
            'ratingLabels' => ['1 star', '2 stars', '3 stars', '4 stars', '5 stars'],
            'ratingValues' => $ratingValues,
            'recentUsers' => $recentUsers,
            'recentSubmissions' => $recentSubmissions,
            'topDrivers' => $topDrivers,
            'cohorts' => $cohorts,
            'recentSignups' => $recentSignups,
            'recentActivity' => $recentActivity,
            'topUsers' => $topUsers,
            'recentRatings' => $recentRatings,
            'recentPayments' => $recentPayments,
            'dailyRevenue' => $dailyRevenue,
        ]);
    }

    public function exportUsers(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Email', 'Name', 'Tier', 'Role', 'Signup Date', 'Pro Expires', 'Generations Used', 'Last Generation', 'RSS Token', 'Zernio Connected']);

            User::query()->orderByDesc('created_at')->chunk(500, function ($users) use ($out) {
                foreach ($users as $user) {
                    fputcsv($out, [
                        $user->id,
                        $user->email,
                        $user->name,
                        $user->tier,
                        $user->role,
                        optional($user->created_at)->toDateTimeString(),
                        optional($user->pro_expires_at)->toDateTimeString(),
                        $user->generations_used,
                        optional($user->last_generation_at)->toDateTimeString(),
                        $user->z_rss_token ? 'Yes' : 'No',
                        $user->zernio_api_key ? 'Yes' : 'No',
                    ]);
                }
            });

            fclose($out);
        }, 'postsmith_users_'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
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

    private function resolveDateRange(Request $request): array
    {
        $range = $request->string('range', '30d')->toString();
        $customStart = $request->string('start')->toString();
        $customEnd = $request->string('end')->toString();

        if ($range === 'custom' && $customStart !== '' && $customEnd !== '') {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
        } else {
            $days = ['7d' => 7, '30d' => 30, '90d' => 90, '6m' => 180, '1y' => 365][$range] ?? 30;
            $startDate = now()->subDays($days)->startOfDay();
            $endDate = now()->endOfDay();
        }

        return [$range, $customStart, $customEnd, $startDate, $endDate];
    }

    private function buildCohorts(): array
    {
        $users = User::query()
            ->where('created_at', '>=', now()->subDays(90))
            ->orderBy('created_at')
            ->get(['id', 'created_at']);

        $cohorts = [];

        foreach ($users as $user) {
            $signup = $user->created_at->copy()->startOfDay();
            $week = $signup->copy()->startOfWeek()->toDateString();

            $cohorts[$week] ??= ['total' => 0, 'active1d' => 0, 'active7d' => 0, 'active30d' => 0];
            $cohorts[$week]['total']++;

            foreach ([1 => 'active1d', 7 => 'active7d', 30 => 'active30d'] as $days => $key) {
                $hasActivity = GenerationHistory::where('user_id', $user->id)
                    ->where('created_at', '>=', $signup)
                    ->where('created_at', '<=', $signup->copy()->addDays($days)->endOfDay())
                    ->exists();

                if ($hasActivity) {
                    $cohorts[$week][$key]++;
                }
            }
        }

        ksort($cohorts);

        return array_slice($cohorts, -8, 8, true);
    }
}
