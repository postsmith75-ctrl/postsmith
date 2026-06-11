@extends('admin.layout')

@section('title', 'Drivers')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold logo-text text-slate-950">Discovered Drivers</h1>
        <p class="text-sm text-slate-500 mt-1">Promote or reject drivers discovered through Viral Lab.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach ($drivers as $driver)
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">{{ $driver->driver_name }}</h2>
                        <p class="text-xs text-slate-400">{{ $driver->submissions_count }} submissions • {{ round($driver->avg_confidence, 1) }}% confidence</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded {{ $driver->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($driver->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500') }}">{{ $driver->status }}</span>
                </div>
                <p class="text-sm text-slate-600 leading-6 mb-3">{{ $driver->description ?: 'No description yet.' }}</p>
                <p class="text-xs text-slate-500 leading-5 mb-4">{{ $driver->psychology ?: 'No psychology note yet.' }}</p>
                <form method="POST" action="{{ route('admin.drivers.update', $driver) }}" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 bg-white">
                        <option value="pending" @selected($driver->status === 'pending')>Pending</option>
                        <option value="active" @selected($driver->status === 'active')>Active</option>
                        <option value="rejected" @selected($driver->status === 'rejected')>Rejected</option>
                    </select>
                    <button type="submit" class="bg-slate-900 text-white font-bold px-4 py-2 rounded-lg hover:bg-slate-800">Save</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-5">{{ $drivers->links() }}</div>
@endsection
