@extends('layouts.admin')
@section('title', 'Audit Trail')
@section('pageDescription', 'Review all system actions — logins, creates, updates, and deletes.')
@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-5">
        @foreach($stats as $s)
            @php
                $color = match($s['label']){
                    'Logins'  => 'text-sky-600',
                    'Creates' => 'text-emerald-600',
                    'Updates' => 'text-amber-600',
                    'Deletes' => 'text-rose-600',
                    default   => 'text-slate-900',
                };
            @endphp
            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-5 text-center">
                <p class="text-xs uppercase tracking-widest font-semibold text-slate-500">{{ $s['label'] }}</p>
                <p class="mt-3 text-4xl font-black {{ $color }}">{{ $s['value'] }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">System Audit Log</h2>
                <p class="text-sm text-slate-500 mt-1">All recorded user actions. Filter by user, role, or action type.</p>
            </div>
            <form method="GET" action="{{ route('admin.audit-trail') }}" class="flex flex-wrap gap-3">
                <input type="text" name="user" value="{{ $userFilter }}" placeholder="Search user…"
                       class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-green-400">
                <select name="role" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="">All Roles</option>
                    <option value="admin"   @selected($roleFilter==='admin')>Admin</option>
                    <option value="faculty" @selected($roleFilter==='faculty')>Faculty</option>
                    <option value="student" @selected($roleFilter==='student')>Student</option>
                </select>
                <select name="action" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="">All Actions</option>
                    <option value="Login"  @selected($actionFilter==='Login') >Login</option>
                    <option value="Logout" @selected($actionFilter==='Logout')>Logout</option>
                    <option value="Create" @selected($actionFilter==='Create')>Create</option>
                    <option value="Update" @selected($actionFilter==='Update')>Update</option>
                    <option value="Delete" @selected($actionFilter==='Delete')>Delete</option>
                </select>
                <input type="date" name="date" value="{{ $dateFilter }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                <button type="submit" class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">Filter</button>
                @if($userFilter || $roleFilter || $actionFilter || $dateFilter)
                    <a href="{{ route('admin.audit-trail') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Timestamp</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">User</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Role</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Action</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Module</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Detail</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($actions as $action)
                        @php
                            $actionBadge = match($action['action']){
                                'Login','Logout' => 'bg-slate-100 text-slate-600',
                                'Create' => 'bg-emerald-100 text-emerald-700',
                                'Update' => 'bg-amber-100 text-amber-700',
                                'Delete' => 'bg-rose-100 text-rose-700',
                                default  => 'bg-slate-100 text-slate-600',
                            };
                            $roleBadge = match($action['role']){
                                'admin'   => 'bg-violet-100 text-violet-700',
                                'faculty' => 'bg-sky-100 text-sky-700',
                                default   => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap text-xs">{{ $action['time'] }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-900 whitespace-nowrap">{{ $action['user'] }}</td>
                            <td class="px-5 py-3.5"><span class="inline-flex rounded-full {{ $roleBadge }} px-2.5 py-0.5 text-xs font-semibold capitalize">{{ $action['role'] }}</span></td>
                            <td class="px-5 py-3.5 text-center"><span class="inline-flex rounded-full {{ $actionBadge }} px-3 py-1 text-xs font-bold">{{ $action['action'] }}</span></td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $action['module'] }}</td>
                            <td class="px-5 py-3.5 text-slate-500 max-w-xs truncate">{{ $action['detail'] }}</td>
                            <td class="px-5 py-3.5 text-slate-400 font-mono text-xs">{{ $action['ip'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">No audit records match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $pagination->links() }}
        </div>
    </section>
</div>
@endsection
