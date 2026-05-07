@extends('layouts.student')

@section('title', 'Enrollment')
@section('pageDescription', 'Browse available modules and enroll in your next classes.')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-medium text-rose-800">{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- Header --}}
        <section class="rounded-3xl bg-gradient-to-r from-green-500 to-emerald-600 p-6 shadow-md text-white">
            <div class="flex flex-wrap items-center gap-4 justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Enrollment Center</h2>
                    <p class="mt-1 text-green-100 text-sm">Select from available modules and build your class load for this term.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-4 py-1.5 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        {{ count($availableModules) }} Available
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-4 py-1.5 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ count($enrolledModules) }} Enrolled
                    </span>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
            {{-- Available Modules --}}
            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-bold text-slate-900">Available Modules</h3>
                    <span class="text-xs text-slate-400">{{ count($availableModules) }} remaining</span>
                </div>
                <p class="text-sm text-slate-500 mb-6">Click <strong>Enroll Now</strong> to add a module. Your enrollment will be reviewed.</p>

                <div class="space-y-4">
                    @forelse($availableModules as $module)
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 hover:border-green-300 hover:bg-green-50/30 transition-all">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-bold text-slate-900">{{ $module['name'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-500">{{ $module['description'] }}</p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">{{ $module['code'] }}</span>
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">{{ $module['units'] }} Units</span>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                                <p><span class="font-semibold text-slate-800">Instructor:</span> {{ $module['instructor'] }}</p>
                                <p><span class="font-semibold text-slate-800">Schedule:</span> {{ $module['schedule'] }}</p>
                            </div>

                            <form method="POST" action="{{ route('student.enrollment.store') }}" class="mt-5">
                                @csrf
                                <input type="hidden" name="module_code" value="{{ $module['code'] }}">
                                @if($isEnrollmentPeriod)
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-green-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-700 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Enroll Now
                                    </button>
                                @else
                                    <button type="button" disabled class="inline-flex items-center gap-2 rounded-2xl bg-slate-200 px-5 py-2 text-sm font-semibold text-slate-400 cursor-not-allowed shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Enrollment Closed
                                    </button>
                                @endif
                            </form>
                        </article>
                    @empty
                        <article class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                            <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm font-medium text-slate-700">You have enrolled in all available modules.</p>
                            <p class="mt-1 text-sm text-slate-500">Check your current schedule on the right panel.</p>
                        </article>
                    @endforelse
                </div>
            </section>

            {{-- Current Enrollment --}}
            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-1">My Enrollments</h3>
                <p class="text-sm text-slate-500 mb-6">Your enrolled modules for this term with their current status.</p>

                <div class="space-y-4">
                    @forelse($enrolledModules as $module)
                        @php
                            $status = $module['status'] ?? 'pending';
                            $statusConfig = match($status) {
                                'enrolled' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'card' => 'bg-emerald-50/40', 'label' => 'Enrolled'],
                                'dropped'  => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'border' => 'border-rose-200',    'card' => 'bg-rose-50/30',    'label' => 'Dropped'],
                                default    => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'border' => 'border-amber-200',   'card' => 'bg-amber-50/30',   'label' => 'Pending'],
                            };
                        @endphp
                        <article class="rounded-3xl border {{ $statusConfig['border'] }} {{ $statusConfig['card'] }} p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 truncate">{{ $module['name'] }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $module['code'] }}</p>
                                </div>
                                <span class="inline-flex flex-shrink-0 items-center rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} px-3 py-1 text-xs font-bold">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1 text-xs text-slate-600">
                                <p><span class="font-semibold text-slate-800">Instructor:</span> {{ $module['instructor'] }}</p>
                                <p><span class="font-semibold text-slate-800">Schedule:</span> {{ $module['schedule'] }}</p>
                                @if($module['section'])
                                    <p><span class="font-semibold text-slate-800">Section:</span>
                                        <span class="inline-flex rounded-md bg-sky-100 px-2 py-0.5 text-sky-700 font-semibold">{{ $module['section'] }}</span>
                                    </p>
                                @else
                                    <p class="text-slate-400 italic">Section: To be assigned</p>
                                @endif
                                @if($module['enrolled_on'])
                                    <p><span class="font-semibold text-slate-800">Enrolled:</span> {{ $module['enrolled_on'] }}</p>
                                @endif
                            </div>

                            @if($status !== 'dropped')
                                <form method="POST" action="{{ route('student.enrollment.drop', $module['id']) }}" class="mt-4"
                                      onsubmit="return confirm('Are you sure you want to drop {{ $module['name'] }}? This action cannot be undone.')">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-white border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Drop Course
                                    </button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <article class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                            <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <p class="text-sm font-medium text-slate-700">You are not enrolled in any module yet.</p>
                            <p class="mt-1 text-sm text-slate-500">Use the enrollment list on the left to get started.</p>
                        </article>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Status Legend --}}
        <section class="rounded-3xl bg-white p-5 shadow-sm border border-slate-200">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Enrollment Status Guide</p>
            <div class="flex flex-wrap gap-4 text-xs">
                <span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-amber-400"></span> <strong class="text-slate-800">Pending</strong> — Awaiting review by faculty/admin</span>
                <span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-emerald-500"></span> <strong class="text-slate-800">Enrolled</strong> — Confirmed and approved</span>
                <span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-rose-400"></span> <strong class="text-slate-800">Dropped</strong> — Course has been dropped</span>
            </div>
        </section>
    </div>
@endsection
