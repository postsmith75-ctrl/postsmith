@extends('admin.layout')

@section('title', 'Viral Lab')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold logo-text text-slate-950">Viral Lab Submissions</h1>
        <p class="text-sm text-slate-500 mt-1">Review analyzed posts and driver discovery signals.</p>
    </div>

    <div class="space-y-4">
        @forelse ($submissions as $submission)
            <article class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-sm font-bold text-slate-950">{{ $submission->platform }}</p>
                        <p class="text-xs text-slate-400">{{ $submission->user?->email ?: 'Unknown user' }} • {{ $submission->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2 py-1 rounded bg-slate-100 text-slate-600">{{ number_format($submission->likes) }} likes</span>
                        <span class="px-2 py-1 rounded bg-slate-100 text-slate-600">{{ number_format($submission->comments) }} comments</span>
                        <span class="px-2 py-1 rounded bg-slate-100 text-slate-600">{{ number_format($submission->shares) }} shares</span>
                    </div>
                </div>
                <p class="text-sm text-slate-700 leading-6 mb-4">{{ str($submission->post_text)->limit(420) }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (($submission->detected_drivers ?: []) as $driver)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $driver }}</span>
                    @endforeach
                    @if ($submission->new_driver_flag)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100">New: {{ $submission->new_driver_name }}</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center text-slate-400">No Viral Lab submissions yet.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $submissions->links() }}</div>
@endsection
