@extends('admin.layout')

@section('title', 'Users')

@section('content')
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold logo-text text-white">Users</h1>
            <p class="text-sm text-slate-400 mt-1">Manage tiers, roles, and subscription windows.</p>
        </div>
    </div>

    <div class="admin-surface rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="admin-table-head text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-4 py-3">User</th>
                        <th class="text-left px-4 py-3">Usage</th>
                        <th class="text-left px-4 py-3">Access</th>
                        <th class="text-left px-4 py-3">Expires</th>
                        <th class="text-right px-4 py-3">Update</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($users as $user)
                        <tr class="admin-row">
                            <td class="px-4 py-4 min-w-[240px]">
                                <p class="font-bold text-white">{{ $user->name ?: 'Unnamed user' }}</p>
                                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-300">
                                <p>{{ number_format($user->generations_used) }} generations used</p>
                                <p class="text-xs text-slate-400">{{ $user->generation_history_count }} generations, {{ $user->posts_count }} posts</p>
                            </td>
                            <td class="px-4 py-4">
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex flex-col sm:flex-row gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="tier" class="admin-input rounded-lg px-2 py-2">
                                        <option value="free" @selected($user->tier === 'free')>Free</option>
                                        <option value="starter" @selected($user->tier === 'starter')>Starter</option>
                                        <option value="pro" @selected($user->tier === 'pro')>Pro</option>
                                    </select>
                                    <select name="role" class="admin-input rounded-lg px-2 py-2">
                                        <option value="user" @selected($user->role === 'user')>User</option>
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                        <option value="owner" @selected($user->role === 'owner')>Owner</option>
                                    </select>
                            </td>
                            <td class="px-4 py-4">
                                    <select name="expires_in" class="admin-input rounded-lg px-2 py-2">
                                        <option value="none">Keep current</option>
                                        <option value="month">Set +1 month</option>
                                        <option value="year">Set +1 year</option>
                                    </select>
                                    <p class="text-xs text-slate-400 mt-1">{{ $user->pro_expires_at?->format('M j, Y') ?: 'No expiry' }}</p>
                            </td>
                            <td class="px-4 py-4 text-right">
                                    <button type="submit" class="bg-indigo-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-indigo-500">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-800">{{ $users->links() }}</div>
    </div>
@endsection
