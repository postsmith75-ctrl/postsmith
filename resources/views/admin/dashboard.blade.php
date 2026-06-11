@extends('admin.layout')

@section('title', 'Overview')

@section('content')
    <div class="mb-6">
        <p class="text-sm font-bold text-indigo-600 uppercase tracking-wide">Owner console</p>
        <h1 class="text-3xl font-bold logo-text text-slate-950 mt-1">PostSmith Admin</h1>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach ($stats as $label => $value)
            <div class="bg-white border border-slate-200 rounded-2xl p-4">
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">{{ str_replace('_', ' ', $label) }}</p>
                <p class="text-2xl font-bold text-slate-950 mt-2">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <section class="bg-white border border-slate-200 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-950">Recent Users</h2>
                <a href="{{ route('admin.users') }}" class="text-xs font-bold text-indigo-600">View all</a>
            </div>
            <div class="space-y-3">
                @foreach ($recentUsers as $user)
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ $user->name ?: 'Unnamed user' }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 rounded bg-slate-100 text-slate-600">{{ $user->tier }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-950">Recent Viral Lab</h2>
                <a href="{{ route('admin.viral-lab') }}" class="text-xs font-bold text-indigo-600">View all</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentSubmissions as $submission)
                    <div>
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $submission->platform }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $submission->user?->email ?: 'Unknown user' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No submissions yet.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl p-5">
            <h2 class="text-lg font-bold text-slate-950 mb-4">Top Drivers</h2>
            <div class="space-y-3">
                @forelse ($topDrivers as $driver)
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $driver->driver }}</p>
                        <span class="text-xs text-slate-500">{{ number_format($driver->score ?? 0) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No tracked drivers yet.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
