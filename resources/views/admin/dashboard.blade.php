@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('pageDescription', 'Monitor system health, users, and school analytics.')

@section('content')
    <div class="space-y-6">
        {{-- Welcome Banner --}}
        <div class="rounded-3xl bg-gradient-to-r from-green-500 to-green-600 p-8 shadow-md text-white flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold mb-1">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-green-50 text-sm">Here's a live summary of your school's performance and system status.</p>
            </div>
            <a href="{{ route('admin.enrollments') }}" class="inline-flex items-center gap-2 rounded-2xl bg-white/20 hover:bg-white/30 px-5 py-2.5 text-sm font-semibold text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Manage Enrollment per Subject →
            </a>
        </div>

        {{-- Pending Verification Alert --}}
        @if($pendingUsersCount > 0)
        <div class="bg-amber-100 text-amber-800 p-4 rounded-xl border border-amber-200 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div class="font-semibold text-sm">New Student Accounts for Verification ({{ $pendingUsersCount }} pending)</div>
            </div>
            <a href="{{ route('admin.users', ['status' => 'pending']) }}" class="text-sm font-bold bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition">View Users</a>
        </div>
        @endif

        {{-- Key Metrics Grid --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($summary as $item)
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">{{ $item['label'] }}</p>
                    <div class="mt-4 flex items-center justify-between gap-4">
                        <p class="text-4xl font-bold text-slate-900">{{ $item['value'] }}</p>
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-green-50 text-green-600 shadow-sm border border-green-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Balanced 3-Column Section --}}
        <div class="grid gap-6 xl:grid-cols-3">

            {{-- Quick Stats --}}
            <section class="xl:col-span-1 rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-5">Quick Stats</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($overview as $item)
                        <article class="group rounded-2xl bg-slate-50 p-4 border border-slate-100 hover:border-green-400 hover:bg-green-50/30 transition-all">
                            <p class="text-xs font-semibold text-slate-500 leading-tight">{{ $item['title'] }}</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $item['value'] }}</p>
                        </article>
                    @endforeach
                </div>
                <div class="mt-4 rounded-2xl bg-slate-50 p-4 border border-slate-100">
                    <div class="flex items-center justify-between text-sm text-slate-600 mb-2">
                        <span class="font-semibold text-slate-700">Server Usage</span>
                        <span class="font-bold text-slate-900">68%</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full w-2/3 rounded-full bg-green-500"></div>
                    </div>
                </div>
            </section>

            {{-- Student Analytics by Level --}}
            <section class="xl:col-span-1 rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-slate-900">Students by Level</h2>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($levelStats as $stat)
                        @php
                            $total = array_sum(array_column($levelStats, 'count')) ?: 1;
                            $pct = round(($stat['count'] / $total) * 100);
                            $bar = match(true) {
                                str_contains($stat['label'], 'Senior') => 'bg-violet-500',
                                str_contains($stat['label'], '1st')    => 'bg-sky-500',
                                str_contains($stat['label'], '2nd')    => 'bg-amber-500',
                                default                                 => 'bg-emerald-500',
                            };
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-semibold text-slate-700">{{ $stat['label'] }}</span>
                                <span class="font-bold text-slate-900">{{ $stat['count'] }} <span class="text-slate-400 font-normal">({{ $pct }}%)</span></span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full {{ $bar }} rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{-- Course breakdown --}}
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">By Course</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($courseStats as $cs)
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 text-center">
                                <p class="text-xs font-bold text-green-700">{{ $cs['label'] }}</p>
                                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $cs['count'] }}</p>
                                <p class="text-xs text-slate-400">students</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- System Status --}}
            <aside class="xl:col-span-1 rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h2 class="text-lg font-bold text-slate-900 mb-5">System Status</h2>
                <div class="space-y-3">
                    @foreach($recentActions as $action)
                        <div class="group rounded-2xl bg-slate-50 p-4 border border-slate-100 hover:border-green-400 hover:bg-green-50/30 transition-all">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex-shrink-0 inline-flex h-7 w-7 items-center justify-center rounded-full bg-green-100 text-green-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-green-700 transition-colors">{{ $action['title'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $action['subtitle'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>
    </div>
@endsection
