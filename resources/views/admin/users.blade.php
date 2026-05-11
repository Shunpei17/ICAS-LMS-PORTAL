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
        <div class="mb-8 p-6 bg-slate-50 rounded-3xl border border-slate-100">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Bulk User Management
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Easily add multiple student or admin accounts via CSV upload.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 rounded-2xl bg-white border border-slate-200 p-1.5 shadow-sm">
                        <a href="{{ route('admin.users.template.student') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 rounded-xl transition">
                            <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Student Template
                        </a>
                        <div class="w-px h-4 bg-slate-200"></div>
                        <a href="{{ route('admin.users.template.faculty') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 rounded-xl transition">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Faculty Template
                        </a>
                        <div class="w-px h-4 bg-slate-200"></div>
                        <a href="{{ route('admin.users.template.admin') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 rounded-xl transition">
                            <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Admin Template
                        </a>
                    </div>

                    <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <label class="cursor-pointer flex items-center gap-2 rounded-2xl bg-green-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-green-700 transition shadow-md shadow-green-200/50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Import CSV
                            <input type="file" name="csv_file" accept=".csv,text/csv" class="hidden" onchange="this.form.submit()">
                        </label>
                    </form>
                </div>
            </div>

            @if($errors->has('csv_errors'))
                <div class="mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-100 text-xs text-rose-700 leading-relaxed whitespace-pre-line font-mono">
                    <span class="font-bold uppercase tracking-widest block mb-1">Import Errors:</span>
                    {{ $errors->first('csv_errors') }}
                </div>
            @endif
            @if(session('import_result'))
                <div class="mt-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-800 flex items-center gap-4">
                    <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Success: <strong>{{ session('import_result.success') }}</strong></div>
                    <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-rose-500"></span> Failed: <strong>{{ session('import_result.failed') }}</strong></div>
                    <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Duplicates: <strong>{{ session('import_result.duplicates') }}</strong></div>
                </div>
            @endif
        </div>

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
                                    <a href="{{ route('admin.users.show', $user['id']) }}" class="rounded-lg bg-slate-100 p-2 text-slate-600 hover:bg-slate-200 transition" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user['id']) }}" class="rounded-lg bg-amber-100 p-2 text-amber-700 hover:bg-amber-200 transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    
                                    <div class="flex items-center gap-2 border-l border-slate-200 pl-2 ml-1">
                                        @if($user['status']==='active')
                                            <form method="POST" action="{{ route('admin.users.activate', $user['id']) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="inactive">
                                                <button type="submit" class="rounded-lg bg-rose-50 p-2 text-rose-600 hover:bg-rose-100 transition" title="Deactivate">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.activate', $user['id']) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="rounded-lg bg-emerald-50 p-2 text-emerald-600 hover:bg-emerald-100 transition" title="Activate">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.users.delete', $user['id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-slate-100 p-2 text-slate-500 hover:bg-rose-500 hover:text-white transition" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
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
