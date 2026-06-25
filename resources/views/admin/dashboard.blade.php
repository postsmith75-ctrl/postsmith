@extends('admin.layout')

@section('title', 'Growth Dashboard')

@section('content')
@php
    $trendClass = fn ($value) => $value >= 0 ? 'positive' : 'negative';
    $trendArrow = fn ($value) => $value >= 0 ? '&#9650;' : '&#9660;';
    $tierClass = function ($tier) {
        return match ($tier) {
            'pro' => 'bg-purple-50 text-purple-700 border border-purple-200',
            'starter' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
            default => 'bg-gray-100 text-gray-600 border border-gray-200',
        };
    };
    $ago = function ($date) {
        if (! $date) return '';
        $seconds = now()->diffInSeconds($date);
        if ($seconds < 60) return 'just now';
        if ($seconds < 3600) return floor($seconds / 60).'m ago';
        if ($seconds < 86400) return floor($seconds / 3600).'h ago';
        return floor($seconds / 86400).'d ago';
    };
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- admin-dark.css handles dark admin styles -->

<div class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">PostSmith Admin</h1>
            <div class="dashboard-meta flex flex-wrap items-center gap-2 text-xs mt-2">
                <span class="w-2 h-2 rounded-full bg-green-500 pulse-dot"></span>
                <span>Live data as of {{ now()->format('M j, Y g:i A') }}</span>
                <span class="hidden sm:inline mx-1">|</span>
                <span>Range: <strong class="font-medium">{{ $startDate->format('M j') }} &mdash; {{ $endDate->format('M j, Y') }}</strong></span>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2 flex-wrap">
                <select name="range" onchange="this.form.submit()" class="dashboard-control text-sm rounded-lg px-3 py-2">
                    <option value="7d" @selected($range === '7d')>Last 7 Days</option>
                    <option value="30d" @selected($range === '30d')>Last 30 Days</option>
                    <option value="90d" @selected($range === '90d')>Last 90 Days</option>
                    <option value="6m" @selected($range === '6m')>Last 6 Months</option>
                    <option value="1y" @selected($range === '1y')>Last 1 Year</option>
                    <option value="custom" @selected($range === 'custom')>Custom</option>
                </select>
                @if ($range === 'custom')
                    <input type="date" name="start" value="{{ $customStart }}" class="dashboard-control text-sm rounded-lg px-2 py-2">
                    <input type="date" name="end" value="{{ $customEnd }}" class="dashboard-control text-sm rounded-lg px-2 py-2">
                    <button type="submit" class="bg-indigo-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-indigo-700">Apply</button>
                @endif
            </form>
            <a href="{{ route('admin.users.export') }}" class="dashboard-control text-xs px-3 py-2 rounded-lg transition">Export Users</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Total Users</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</div>
            <div class="text-xs mt-1 {{ $trendClass($wowGrowth) }}">{!! $trendArrow($wowGrowth) !!} {{ abs($wowGrowth) }}% WoW</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">New Signups</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($newUsers) }}</div>
            <div class="text-xs mt-1 {{ $trendClass($momGrowth) }}">{!! $trendArrow($momGrowth) !!} {{ abs($momGrowth) }}% MoM</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Active Users</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($activeUsers) }}</div>
            <div class="text-xs mt-1 neutral">{{ $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0 }}% of total</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Paying Users</p>
            <div class="text-2xl font-bold text-emerald-600">{{ number_format($payingUsers) }}</div>
            <div class="text-xs mt-1 positive">{{ $conversionRate }}% conversion</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">MRR</p>
            <div class="text-2xl font-bold text-emerald-600">${{ number_format($mrr) }}</div>
            <div class="text-xs mt-1 neutral">${{ number_format($arr) }} ARR</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Revenue Today</p>
            <div class="text-2xl font-bold text-emerald-600">${{ number_format($todayRevenue, 2) }}</div>
            <div class="text-xs mt-1 neutral">Actual paid receipts</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Revenue Range</p>
            <div class="text-2xl font-bold text-emerald-600">${{ number_format($rangeRevenue, 2) }}</div>
            <div class="text-xs mt-1 neutral">{{ number_format($paidTransactions) }} paid {{ Str::plural('payment', $paidTransactions) }}</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Platform Total</p>
            <div class="text-2xl font-bold text-emerald-600">${{ number_format($totalRevenue, 2) }}</div>
            <div class="text-xs mt-1 neutral">All-time collected</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Avg Rating</p>
            <div class="text-2xl font-bold star-gold">
                {!! $avgRating > 0 ? $avgRating.' <span class="text-lg">&#9733;</span>' : '&mdash;' !!}
            </div>
            <div class="text-xs mt-1 neutral">{{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Recent Payments</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-500 text-xs uppercase">
                            <th class="pb-3 pr-4">User</th>
                            <th class="pb-3 pr-4">Plan</th>
                            <th class="pb-3 pr-4 text-right">Amount</th>
                            <th class="pb-3 text-right">Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentPayments as $payment)
                            <tr class="table-row">
                                <td class="py-3 pr-4">
                                    <div class="text-gray-800 font-medium">{{ $payment->user?->name ?: Str::before((string) $payment->user?->email, '@') ?: 'Unknown user' }}</div>
                                    <div class="text-xs text-gray-500">{{ $payment->user?->email }}</div>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $tierClass($payment->tier) }}">{{ ucfirst($payment->tier) }}</span>
                                    <div class="text-xs text-gray-500 mt-1">{{ ucfirst($payment->plan) }} · {{ $payment->auto_renew_requested ? 'Auto-renew' : 'Manual' }}</div>
                                </td>
                                <td class="py-3 pr-4 text-right text-emerald-600 font-bold">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="py-3 text-right text-gray-400 text-xs">{{ $payment->paid_at?->format('M j, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm">No paid transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Daily Revenue</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-500 text-xs uppercase">
                            <th class="pb-3 pr-4">Day</th>
                            <th class="pb-3 pr-4 text-center">Payments</th>
                            <th class="pb-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($dailyRevenue as $day)
                            <tr class="table-row">
                                <td class="py-3 pr-4 text-gray-800 font-medium">{{ \Carbon\Carbon::parse($day->day)->format('M j, Y') }}</td>
                                <td class="py-3 pr-4 text-center text-gray-600">{{ number_format($day->payments_count) }}</td>
                                <td class="py-3 text-right text-emerald-600 font-bold">${{ number_format((float) $day->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-6 text-center text-gray-400 text-sm">No revenue in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">RSS Users</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($rssUsers) }}</div>
            <div class="text-xs mt-1 neutral">Starter+ feature</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Zernio Connected</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($zernioUsers) }}</div>
            <div class="text-xs mt-1 neutral">Pro feature</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Generations</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalGenerations) }}</div>
            <div class="text-xs mt-1 neutral">{{ $genPerUser }} per active user</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Churn Rate</p>
            <div class="text-2xl font-bold {{ $churnRate > 5 ? 'negative' : 'positive' }}">{{ $churnRate }}%</div>
            <div class="text-xs mt-1 neutral">{{ $churned }} churned this period</div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Viral Lab</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($viralSubmissions) }}</div>
            <div class="text-xs mt-1 neutral">submissions this period</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Saved Posts</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($postsSaved) }}</div>
            <div class="text-xs mt-1 neutral">tracked this period</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Active Drivers</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['active_drivers']) }}</div>
            <div class="text-xs mt-1 neutral">available in app</div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Pending Drivers</p>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending_drivers']) }}</div>
            <div class="text-xs mt-1 neutral">awaiting review</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">User Signups</h3>
            <div class="chart-container"><canvas id="signupChart"></canvas></div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Generations / Day</h3>
            <div class="chart-container"><canvas id="genChart"></canvas></div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Rating Distribution</h3>
            <div class="chart-container"><canvas id="ratingChart"></canvas></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Tier Distribution</h3>
            <div class="chart-container" style="height:240px"><canvas id="tierChart"></canvas></div>
        </div>
        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Platform Usage</h3>
            <div class="chart-container" style="height:240px"><canvas id="platChart"></canvas></div>
        </div>
    </div>

    <div class="kpi-card rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Recent Ratings</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500 text-xs uppercase">
                        <th class="pb-3 pr-4">Rating</th>
                        <th class="pb-3 pr-4">User</th>
                        <th class="pb-3 pr-4">Feedback</th>
                        <th class="pb-3 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentRatings as $rating)
                        <tr class="table-row">
                            <td class="py-3 pr-4 star-gold text-base">{!! str_repeat('&#9733;', $rating->rating).str_repeat('&#9734;', 5 - $rating->rating) !!}</td>
                            <td class="py-3 pr-4">
                                <div class="text-gray-800 font-medium">{{ $rating->name ?: 'Anonymous' }}</div>
                                @if ($rating->email)<div class="text-xs text-gray-500">{{ $rating->email }}</div>@endif
                            </td>
                            <td class="py-3 pr-4 text-gray-600">{{ $rating->feedback ?: 'No feedback' }}</td>
                            <td class="py-3 text-right text-gray-400 text-xs">{{ \Carbon\Carbon::parse($rating->created_at)->format('M j') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm">No ratings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="kpi-card rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Cohort Retention (Weekly Signups)</h3>
        <p class="text-xs text-gray-500 mb-4">% of users who generated at least one post after signup</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500 text-xs uppercase">
                        <th class="pb-3 pr-4">Cohort Week</th>
                        <th class="pb-3 pr-4 text-center">Users</th>
                        <th class="pb-3 pr-4 text-center">Day 1</th>
                        <th class="pb-3 pr-4 text-center">Day 7</th>
                        <th class="pb-3 text-center">Day 30</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($cohorts as $week => $cohort)
                        @php
                            $d1 = $cohort['total'] > 0 ? round(($cohort['active1d'] / $cohort['total']) * 100, 1) : 0;
                            $d7 = $cohort['total'] > 0 ? round(($cohort['active7d'] / $cohort['total']) * 100, 1) : 0;
                            $d30 = $cohort['total'] > 0 ? round(($cohort['active30d'] / $cohort['total']) * 100, 1) : 0;
                        @endphp
                        <tr class="table-row">
                            <td class="py-3 pr-4 text-gray-800 font-medium">{{ \Carbon\Carbon::parse($week)->format('M j') }}</td>
                            <td class="py-3 pr-4 text-center text-gray-600">{{ $cohort['total'] }}</td>
                            @php
                                $cohort_pairs = [[$d1, [50, 20]], [$d7, [40, 15]], [$d30, [30, 10]]];
                            @endphp
                            @foreach ($cohort_pairs as $pair)
                                @php
                                    $percent = $pair[0];
                                    $limits = $pair[1];
                                    $bar = $percent >= $limits[0] ? 'bg-emerald-500' : ($percent >= $limits[1] ? 'bg-yellow-500' : 'bg-red-500');
                                    $text = $percent >= $limits[0] ? 'positive' : ($percent >= $limits[1] ? 'text-yellow-600' : 'negative');
                                @endphp
                                <td class="py-3 pr-4 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <span class="w-16 h-2 rounded-full bg-gray-200 overflow-hidden"><span class="block h-full rounded-full {{ $bar }}" data-width="{{ min(100, $percent) }}"></span></span>
                                        <span class="text-xs {{ $text }}">{{ $percent }}%</span>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400 text-sm">No cohort data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Top Performing Drivers</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-500 text-xs uppercase">
                            <th class="pb-3 pr-4">Driver</th>
                            <th class="pb-3 pr-4 text-center">Posts</th>
                            <th class="pb-3 pr-4 text-center">Engagement</th>
                            <th class="pb-3 text-right">Avg/Post</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topDrivers as $driver)
                            @php $avg = $driver->posts_count > 0 ? round($driver->engagement / $driver->posts_count, 1) : 0; @endphp
                            <tr class="table-row">
                                <td class="py-3 pr-4 text-gray-800 font-medium">{{ $driver->driver }}</td>
                                <td class="py-3 pr-4 text-center text-gray-600">{{ number_format($driver->posts_count) }}</td>
                                <td class="py-3 pr-4 text-center text-gray-600">{{ number_format($driver->engagement) }}</td>
                                <td class="py-3 text-right text-emerald-600 font-medium">{{ $avg }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm">No tracked posts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="kpi-card rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Power Users</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-500 text-xs uppercase">
                            <th class="pb-3 pr-4">User</th>
                            <th class="pb-3 pr-4 text-center">Tier</th>
                            <th class="pb-3 text-right">Generations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topUsers as $topUser)
                            <tr class="table-row">
                                <td class="py-3 pr-4">
                                    <div class="text-gray-800 font-medium">{{ $topUser->name ?: Str::before($topUser->email, '@') }}</div>
                                    <div class="text-xs text-gray-500">{{ $topUser->email }}</div>
                                </td>
                                <td class="py-3 pr-4 text-center"><span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $tierClass($topUser->tier) }}">{{ ucfirst($topUser->tier) }}</span></td>
                                <td class="py-3 text-right text-gray-900 font-bold">{{ number_format($topUser->gens) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-6 text-center text-gray-400 text-sm">No generation activity.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="kpi-card rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Recent Signups <span class="text-xs font-normal text-gray-500 ml-2">({{ $recentSignups->count() }} new in this period)</span></h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500 text-xs uppercase">
                        <th class="pb-3 pr-4">User</th>
                        <th class="pb-3 pr-4">Tier</th>
                        <th class="pb-3 pr-4 text-center">Source</th>
                        <th class="pb-3 text-right">Signed Up</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentSignups as $signup)
                        @php $isNew = $signup->created_at->gt(now()->subHour()); @endphp
                        <tr class="table-row {{ $isNew ? 'bg-emerald-50/50' : '' }}">
                            <td class="py-3 pr-4">
                                <div class="text-gray-800 font-medium">{{ $signup->name ?: Str::before($signup->email, '@') }}</div>
                                <div class="text-xs text-gray-500">{{ $signup->email }}</div>
                            </td>
                            <td class="py-3 pr-4"><span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $tierClass($signup->tier) }}">{{ ucfirst($signup->tier) }}</span></td>
                            <td class="py-3 pr-4 text-center"><span class="text-xs text-gray-500">{{ $signup->google_id ? 'Google' : 'Email' }}</span></td>
                            <td class="py-3 text-right">
                                <div class="text-xs font-semibold {{ $isNew ? 'text-emerald-600' : 'text-gray-600' }}">{{ $ago($signup->created_at) }}</div>
                                <div class="text-[10px] text-gray-400">{{ $signup->created_at->format('M j, g:i A') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm">No signups in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="kpi-card rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Recent Activity Feed</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500 text-xs uppercase">
                        <th class="pb-3 pr-4">User</th>
                        <th class="pb-3 pr-4">Action</th>
                        <th class="pb-3 pr-4">Platform</th>
                        <th class="pb-3 text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentActivity as $activity)
                        @php
                            $modeClass = match ($activity->mode) {
                                'scratch' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                'rewrite' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                default => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            };
                        @endphp
                        <tr class="table-row">
                            <td class="py-3 pr-4">
                                <div class="text-gray-800 font-medium">{{ $activity->user?->name ?: Str::before((string) $activity->user?->email, '@') ?: 'Unknown user' }}</div>
                                <div class="text-xs text-gray-500">{{ $activity->user?->email }}</div>
                            </td>
                            <td class="py-3 pr-4"><span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $modeClass }}">{{ ucfirst($activity->mode ?: 'generate') }}</span></td>
                            <td class="py-3 pr-4 text-gray-600">{{ $activity->platform ?: 'General' }}</td>
                            <td class="py-3 text-right text-gray-400 text-xs">{{ $ago($activity->created_at) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm">No activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center text-xs text-gray-400 pt-4 pb-8">
        PostSmith Admin Dashboard &middot; Data refreshes on page load
    </div>
</div>

    <!-- chart data encoded to avoid Blade tokens in inline JS (read by admin-dashboard.js) -->
    <div id="dashboard-data" style="display:none"
        data-chart-labels="{{ base64_encode(json_encode($chartLabels)) }}"
        data-chart-signups="{{ base64_encode(json_encode($chartSignups)) }}"
        data-chart-gens="{{ base64_encode(json_encode($chartGens)) }}"
        data-rating-labels="{{ base64_encode(json_encode($ratingLabels)) }}"
        data-rating-values="{{ base64_encode(json_encode($ratingValues)) }}"
        data-tier-labels="{{ base64_encode(json_encode($tierLabels)) }}"
        data-tier-values="{{ base64_encode(json_encode($tierValues)) }}"
        data-plat-labels="{{ base64_encode(json_encode($platLabels)) }}"
        data-plat-values="{{ base64_encode(json_encode($platValues)) }}"
    ></div>

    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endsection
