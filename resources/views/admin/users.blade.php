@extends('layouts.admin')
@section('title', 'User Management')
@section('pageDescription', 'Manage all students, faculty, and administrators in the system.')
@section('content')
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-5">
        @foreach([['Total',$stats['total'],'slate'],['Students',$stats['students'],'sky'],['Faculty',$stats['faculty'],'emerald'],['Admins',$stats['admins'],'violet'],['Pending',$stats['pending'],'amber']] as [$l,$v,$c])
            @php $clr=match($c){'sky'=>'text-sky-600','emerald'=>'text-emerald-600','violet'=>'text-violet-600','amber'=>'text-amber-600',default=>'text-slate-900'}; @endphp
            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-5 text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">{{ $l }}</p>
                <p class="mt-3 text-4xl font-black {{ $clr }}">{{ $v }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">All Users</h2>
                <p class="text-sm text-slate-500 mt-1">{{ count($filtered) }} user{{ count($filtered)!==1?'s':'' }} shown.</p>
            </div>
            <form method="GET" action="{{ route('admin.users') }}" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search…" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-green-400">
                <select name="role" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="">All Roles</option>
                    <option value="student" @selected($roleFilter==='student')>Student</option>
                    <option value="faculty" @selected($roleFilter==='faculty')>Faculty</option>
                    <option value="admin"   @selected($roleFilter==='admin')>Admin</option>
                </select>
                <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="">All Statuses</option>
                    <option value="active"   @selected($statusFilter==='active')>Active</option>
                    <option value="inactive" @selected($statusFilter==='inactive')>Inactive</option>
                    <option value="pending"  @selected($statusFilter==='pending')>Pending</option>
                </select>
                <button type="submit" class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">Filter</button>
                @if($search||$roleFilter||$statusFilter)
                    <a href="{{ route('admin.users') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">User</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Email</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Role</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Joined</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($filtered as $user)
                        @php
                            $rb=match($user['role']){'admin'=>'bg-violet-100 text-violet-700','faculty'=>'bg-sky-100 text-sky-700',default=>'bg-slate-100 text-slate-600'};
                            $sb=match($user['status']){'active'=>'bg-emerald-100 text-emerald-700','pending'=>'bg-amber-100 text-amber-700',default=>'bg-rose-100 text-rose-700'};
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-green-100 text-green-700 grid place-items-center font-bold text-sm flex-shrink-0">{{ strtoupper(substr($user['name'],0,1)) }}</div>
                                    <p class="font-semibold text-slate-900">{{ $user['name'] }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-500">{{ $user['email'] }}</td>
                            <td class="px-5 py-4 text-center"><span class="inline-flex rounded-full {{ $rb }} px-2.5 py-0.5 text-xs font-semibold capitalize">{{ $user['role'] }}</span></td>
                            <td class="px-5 py-4 text-center"><span class="inline-flex rounded-full {{ $sb }} px-2.5 py-0.5 text-xs font-semibold capitalize">{{ $user['status'] }}</span></td>
                            <td class="px-5 py-4 text-slate-500 text-xs">{{ $user['joined'] }}</td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    @if($user['role'] === 'student' && $user['status'] === 'pending')
                                        @if($user['enrollment_type'] === 'New Student' && $user['receipt_proof'])
                                            <a href="{{ asset('storage/' . $user['receipt_proof']) }}" target="_blank" class="rounded-lg bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-200 transition">View Receipt</a>
                                        @elseif($user['enrollment_type'] === 'Old Student' && $user['student_id_proof'])
                                            <a href="{{ asset('storage/' . $user['student_id_proof']) }}" target="_blank" class="rounded-lg bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-200 transition">View ID</a>
                                        @else
                                            <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">No Doc</span>
                                        @endif
                                    @else
                                        <button class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">View</button>
                                    @endif
                                    <button class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200 transition">Edit</button>
                                    @if($user['status']==='active')
                                        <form method="POST" action="{{ route('admin.users.activate', $user['id']) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200 transition">Deactivate</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.activate', $user['id']) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200 transition">Activate</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">No users match your search.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
