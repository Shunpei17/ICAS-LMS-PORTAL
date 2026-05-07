@extends('layouts.admin')

@section('title', 'Faculty Management')
@section('pageDescription', 'Manage faculty accounts, view teaching loads, and monitor account statuses.')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        {{-- Summary Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-3xl font-bold text-slate-900">{{ $totalFaculty }}</h3>
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider mt-1">Total Faculty</p>
            </div>
            
            @foreach($departmentStats as $stat)
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col items-center justify-center text-center">
                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1z"></path></svg>
                    </div>
                    <h3 class="text-3xl font-bold text-slate-900">{{ $stat['count'] }}</h3>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Filters & Search --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" action="{{ route('admin.faculty') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Search Faculty</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-green-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                </div>

                <div class="w-48">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-green-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-green-500">
                        <option value="">All Statuses</option>
                        <option value="active" @selected($statusFilter === 'active')>Active</option>
                        <option value="inactive" @selected($statusFilter === 'inactive')>Inactive</option>
                        <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
                    </select>
                </div>

                <button type="submit" class="rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Filter Results
                </button>
                @if($search || $statusFilter)
                    <a href="{{ route('admin.faculty') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                        Clear
                    </a>
                @endif
            </form>
        </section>

        {{-- Faculty Directory Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($facultyList as $faculty)
                <div class="relative rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden group hover:shadow-md transition flex flex-col">
                    <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-r from-green-500 to-emerald-600 opacity-20 group-hover:opacity-30 transition"></div>
                    
                    <div class="p-6 relative flex flex-col items-center text-center flex-1">
                        @if($faculty->profile_photo)
                            <img src="{{ asset('storage/' . $faculty->profile_photo) }}" alt="Avatar" class="h-20 w-20 rounded-full object-cover border-4 border-white shadow-sm mb-4">
                        @else
                            @php
                                $initials = collect(explode(' ', trim($faculty->name)))->map(fn($s) => strtoupper(substr($s, 0, 1)))->take(2)->join('');
                            @endphp
                            <div class="h-20 w-20 rounded-full bg-slate-100 border-4 border-white shadow-sm flex items-center justify-center text-2xl font-bold text-slate-400 mb-4">
                                {{ $initials }}
                            </div>
                        @endif

                        <h3 class="text-lg font-bold text-slate-900">{{ $faculty->title ? $faculty->title . ' ' : '' }}{{ $faculty->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1 mb-3">{{ $faculty->email }}</p>

                        <div class="flex flex-wrap items-center justify-center gap-2 mb-4">
                            @if($faculty->status === 'active')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600 uppercase tracking-wider border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                </span>
                            @elseif($faculty->status === 'inactive')
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-600 uppercase tracking-wider border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600 uppercase tracking-wider border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                </span>
                            @endif

                            @if($faculty->department)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 border border-slate-200">{{ $faculty->department }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="border-t border-slate-100 p-4 bg-slate-50 flex items-center gap-2 mt-auto">
                        <a href="{{ route('admin.faculty.show', $faculty) }}" class="flex-1 inline-flex justify-center items-center gap-1.5 rounded-xl bg-white border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:border-green-300 hover:text-green-700 hover:bg-green-50 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            View
                        </a>
                        <form method="POST" action="{{ route('admin.faculty.toggle-status', $faculty) }}" onsubmit="return confirm('Are you sure you want to change this account\'s status?');" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $faculty->status === 'active' ? 'inactive' : 'active' }}">
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold shadow-sm transition {{ $faculty->status === 'active' ? 'bg-white text-rose-600 hover:border-rose-300 hover:bg-rose-50' : 'bg-emerald-600 text-white hover:bg-emerald-700 border-emerald-600' }}">
                                @if($faculty->status === 'active')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    Deactivate
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Activate
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-slate-50/50 p-12 text-center">
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">No faculty found</h3>
                    <p class="text-slate-500 mt-1 max-w-sm mx-auto">Try adjusting your filters or search query to find what you're looking for.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $facultyList->links() }}
        </div>
    </div>
@endsection
