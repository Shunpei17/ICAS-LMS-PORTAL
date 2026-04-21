@extends('layouts.student')

@section('title', 'Enrollment')
@section('pageDescription', 'Browse available modules and enroll in your next classes.')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex flex-wrap items-center gap-4 justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Enrollment Center</h2>
                    <p class="mt-1 text-sm text-slate-500">Select from available modules and build your class load for this term.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                        {{ count($availableModules) }} Available
                    </span>
                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                        {{ count($enrolledModules) }} Enrolled
                    </span>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.25fr_1fr]">
            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900">Available Modules</h3>
                <p class="mt-1 text-sm text-slate-500">Click enroll to add a module to your student dashboard.</p>

                <div class="mt-6 space-y-4">
                    @forelse($availableModules as $module)
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-bold text-slate-900">{{ $module['name'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-500">{{ $module['description'] }}</p>
                                </div>
                                <div class="flex items-center gap-2">
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
                                <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                    Enroll Now
                                </button>
                            </form>
                        </article>
                    @empty
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-sm font-medium text-slate-700">You have enrolled in all available modules.</p>
                            <p class="mt-1 text-sm text-slate-500">Check your current schedule on the right panel.</p>
                        </article>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900">Current Enrollment</h3>
                <p class="mt-1 text-sm text-slate-500">Your enrolled modules for this term.</p>

                <div class="mt-6 space-y-4">
                    @forelse($enrolledModules as $module)
                        <article class="rounded-3xl border border-emerald-200 bg-emerald-50/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="text-sm font-bold text-slate-900">{{ $module['name'] }}</h4>
                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $module['code'] }}</span>
                            </div>
                            <p class="mt-2 text-xs text-slate-600">{{ $module['description'] }}</p>
                            <div class="mt-3 space-y-1 text-xs text-slate-600">
                                <p><span class="font-semibold text-slate-800">Instructor:</span> {{ $module['instructor'] }}</p>
                                <p><span class="font-semibold text-slate-800">Schedule:</span> {{ $module['schedule'] }}</p>
                                @if($module['enrolled_on'])
                                    <p><span class="font-semibold text-slate-800">Enrolled:</span> {{ $module['enrolled_on'] }}</p>
                                @endif
                            </div>
                        </article>
                    @empty
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-sm font-medium text-slate-700">You are not enrolled in any module yet.</p>
                            <p class="mt-1 text-sm text-slate-500">Use the enrollment list to get started.</p>
                        </article>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
