@extends('admin.layout')

@section('title', 'Drivers')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold logo-text text-white">Discovered Drivers</h1>
        <p class="text-sm text-slate-400 mt-1">Promote or reject drivers discovered through Viral Lab.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach ($drivers as $driver)
            <div class="admin-surface rounded-2xl p-5">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <h2 class="text-lg font-bold text-white">{{ $driver->driver_name }}</h2>
                        <p class="text-xs text-slate-400">{{ $driver->submissions_count }} submissions • {{ round($driver->avg_confidence, 1) }}% confidence</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded border {{ $driver->status === 'active' ? 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20' : ($driver->status === 'pending' ? 'bg-amber-500/10 text-amber-300 border-amber-400/20' : 'bg-slate-500/10 text-slate-300 border-slate-500/30') }}">{{ $driver->status }}</span>
                </div>
                <p class="text-sm text-slate-300 leading-6 mb-3">{{ $driver->description ?: 'No description yet.' }}</p>
                <p class="text-xs text-slate-400 leading-5 mb-4">{{ $driver->psychology ?: 'No psychology note yet.' }}</p>
                <form method="POST" action="{{ route('admin.drivers.update', $driver) }}" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="admin-input flex-1 rounded-lg px-3 py-2">
                        <option value="pending" @selected($driver->status === 'pending')>Pending</option>
                        <option value="active" @selected($driver->status === 'active')>Active</option>
                        <option value="rejected" @selected($driver->status === 'rejected')>Rejected</option>
                    </select>
                    <button type="submit" class="bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-indigo-500">Save</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-5">{{ $drivers->links() }}</div>
@endsection
