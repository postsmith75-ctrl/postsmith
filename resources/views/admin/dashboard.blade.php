@extends('admin.layout')

@section('title', 'Growth Dashboard')

@section('content')
@php
    $trendClass = fn ($value) => $value >= 0 ? 'positive' : 'negative';
    $trendArrow = fn ($value) => $value >= 0 ? '&#9650;' : '&#9660;';
    $tierClass = function ($tier) {
        return match ($tier) {
            'pro' => 'bg-purple-500/10 text-purple-200 border border-purple-400/20',
            'starter' => 'bg-indigo-500/10 text-indigo-200 border border-indigo-400/20',
            default => 'bg-slate-500/10 text-slate-300 border border-slate-500/30',
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

<style>
    .kpi-card { background: #071324; border: 1px solid rgba(255,255,255,0.06); box-shadow: 0 18px 50px rgba(2,6,23,0.45); }
    .kpi-card:hover { border-color: #6366f1; transform: translateY(-2px); transition: all 0.3s ease; }
    .positive { color: #34d399; }
    .negative { color: #fb7185; }
    .neutral { color: #94a3b8; }
    .chart-container { position: relative; height: 280px; }
    .table-row:hover { background: rgba(30, 41, 59, 0.72); }
    .pulse-dot { animation: pulse-dot 2s infinite; }
    @keyframes pulse-dot { 0%,100%{ opacity:1; } 50%{ opacity:0.4; } }
    .star-gold { color: #f59e0b; }
    .kpi-card .text-gray-900, .kpi-card .text-gray-800 { color: #f8fafc; }
    .kpi-card .text-gray-700, .kpi-card .text-gray-600 { color: #cbd5e1; }
    .kpi-card .text-gray-500, .kpi-card .text-gray-400 { color: #94a3b8; }
    .kpi-card .border-gray-200, .kpi-card .border-gray-100 { border-color: #1e293b; }
    .kpi-card .divide-gray-100 > :not([hidden]) ~ :not([hidden]) { border-color: #1e293b; }
    .kpi-card .bg-gray-200 { background-color: #334155; }
    .kpi-card .bg-gray-100 { background-color: #1e293b; }
</style>

<div class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">PostSmith Admin</h1>
            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-2">
                <span class="w-2 h-2 rounded-full bg-green-500 pulse-dot"></span>
                <span>Live data as of {{ now()->format('M j, Y g:i A') }}</span>
                <span class="hidden sm:inline mx-1">|</span>
                <span>Range: <span class="text-gray-800 font-medium">{{ $startDate->format('M j') }} &mdash; {{ $endDate->format('M j, Y') }}</span></span>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2 flex-wrap">
                <select name="range" onchange="this.form.submit()" class="admin-input text-sm rounded-lg px-3 py-2">
                    <option value="7d" @selected($range === '7d')>Last 7 Days</option>
                    <option value="30d" @selected($range === '30d')>Last 30 Days</option>
                    <option value="90d" @selected($range === '90d')>Last 90 Days</option>
                    <option value="6m" @selected($range === '6m')>Last 6 Months</option>
                    <option value="1y" @selected($range === '1y')>Last 1 Year</option>
                    <option value="custom" @selected($range === 'custom')>Custom</option>
                </select>
                @if ($range === 'custom')
                    <input type="date" name="start" value="{{ $customStart }}" class="admin-input text-sm rounded-lg px-2 py-2">
                    <input type="date" name="end" value="{{ $customEnd }}" class="admin-input text-sm rounded-lg px-2 py-2">
                    <button type="submit" class="bg-indigo-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-indigo-700">Apply</button>
                @endif
            </form>
            <a href="{{ route('admin.users.export') }}" class="text-xs admin-input text-slate-200 px-3 py-2 rounded-lg hover:border-indigo-400 transition">Export Users</a>
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
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Avg Rating</p>
            <div class="text-2xl font-bold star-gold">
                {!! $avgRating > 0 ? $avgRating.' <span class="text-lg">&#9733;</span>' : '&mdash;' !!}
            </div>
            <div class="text-xs mt-1 neutral">{{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }}</div>
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
                            @foreach ([[$d1, [50, 20]], [$d7, [40, 15]], [$d30, [30, 10]]] as [$percent, $limits])
                                @php
                                    $bar = $percent >= $limits[0] ? 'bg-emerald-500' : ($percent >= $limits[1] ? 'bg-yellow-500' : 'bg-red-500');
                                    $text = $percent >= $limits[0] ? 'positive' : ($percent >= $limits[1] ? 'text-yellow-600' : 'negative');
                                @endphp
                                <td class="py-3 pr-4 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <span class="w-16 h-2 rounded-full bg-gray-200 overflow-hidden"><span class="block h-full rounded-full {{ $bar }}" style="width:{{ min(100, $percent) }}%"></span></span>
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
                                'scratch' => 'bg-blue-500/10 text-blue-200 border border-blue-400/20',
                                'rewrite' => 'bg-purple-500/10 text-purple-200 border border-purple-400/20',
                                default => 'bg-emerald-500/10 text-emerald-200 border border-emerald-400/20',
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

<script>
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#cbd5e1', font: { size: 11 } } } },
        scales: {
            x: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(148,163,184,0.12)' } },
            y: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(148,163,184,0.12)' } }
        }
    };

    new Chart(document.getElementById('signupChart'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{ label: 'New Signups', data: @json($chartSignups), borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#6366f1' }]
        },
        options: commonOptions
    });

    new Chart(document.getElementById('genChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{ label: 'Generations', data: @json($chartGens), backgroundColor: '#8b5cf6', borderRadius: 4 }]
        },
        options: commonOptions
    });

    new Chart(document.getElementById('ratingChart'), {
        type: 'bar',
        data: {
            labels: @json($ratingLabels),
            datasets: [{ label: 'Ratings', data: @json($ratingValues), backgroundColor: ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e'], borderRadius: 4 }]
        },
        options: commonOptions
    });

    new Chart(document.getElementById('tierChart'), {
        type: 'doughnut',
        data: {
            labels: @json($tierLabels),
            datasets: [{ data: @json($tierValues), backgroundColor: ['#64748b', '#6366f1', '#8b5cf6', '#10b981'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { color: '#cbd5e1', font: { size: 11 } } } } }
    });

    new Chart(document.getElementById('platChart'), {
        type: 'bar',
        data: {
            labels: @json($platLabels),
            datasets: [{ label: 'Generations', data: @json($platValues), backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#10b981', '#f59e0b', '#3b82f6'], borderRadius: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { color: 'rgba(148,163,184,0.12)' } }, y: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { display: false } } } }
    });
</script>
@endsection
