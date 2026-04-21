@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('pageDescription', 'Monitor system health, users, and school analytics.')

@section('content')
    <div class="space-y-6 portal-reveal">
        <!-- Welcome Section -->
        <div class="rounded-3xl bg-gradient-to-r from-green-500 to-green-600 p-8 shadow-md text-white">
            <h1 class="text-3xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-green-50">Here's a summary of your school's performance and system status.</p>
        </div>

        <!-- Stats Grid -->
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

        <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <!-- System Overview -->
            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Quick Stats</h2>
                        <p class="text-sm text-slate-500 mt-1">Enrollments, classrooms, attendance & announcements.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($overview as $item)
                        <article class="group rounded-3xl bg-slate-50 p-5 border border-slate-100 hover:border-green-400 hover:bg-green-50/30 transition-all">
                            <p class="text-sm font-semibold text-slate-500">{{ $item['title'] }}</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $item['value'] }}</p>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6 rounded-3xl bg-slate-50 p-5 border border-slate-100">
                    <div class="flex items-center justify-between text-sm text-slate-600 mb-3">
                        <span class="font-semibold">Server Usage</span>
                        <span class="font-bold text-slate-900">68%</span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full w-2/3 rounded-full bg-green-500"></div>
                    </div>
                </div>
            </section>

            <!-- Recent Actions -->
            <aside class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h2 class="text-xl font-bold text-slate-900 mb-6">System Status</h2>
                <div class="space-y-4">
                    @foreach($recentActions as $action)
                        <div class="group rounded-3xl bg-slate-50 p-4 border border-slate-100 hover:border-green-400 hover:bg-green-50/30 transition-all">
                            <div class="flex items-start gap-4">
                                <span class="mt-1 flex-shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-green-700 transition-colors">{{ $action['title'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $action['subtitle'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>
    </div>
@endsection
