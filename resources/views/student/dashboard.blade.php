@extends('layouts.student')

@section('title', 'Welcome, ' . Auth::user()->name . '!')
@section('pageDescription', 'Here\'s your academic overview')

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

        <!-- Stats Grid -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($stats as $stat)
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">{{ $stat['label'] }}</p>
                    <div class="mt-4 flex items-center justify-between gap-4">
                        <p class="text-4xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-{{ $stat['color'] ?? 'green' }}-50 text-{{ $stat['color'] ?? 'green' }}-600 shadow-sm border border-{{ $stat['color'] ?? 'green' }}-100">
                            {!! $stat['icon'] !!}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Main Content Area -->
        <div class="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
            <!-- My Courses -->
            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">My Courses</h2>
                        <p class="text-sm text-slate-500 mt-1">Manage module records and keep this dashboard updated.</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('student.dashboard') }}" class="mb-6 grid gap-3 md:grid-cols-[1.4fr_1fr_1fr_auto_auto]">
                    <input
                        type="text"
                        name="filter_code"
                        value="{{ $filters['filter_code'] }}"
                        placeholder="Filter by module code"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                    />

                    <input
                        type="date"
                        name="filter_due_from"
                        value="{{ $filters['filter_due_from'] }}"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                    />

                    <input
                        type="date"
                        name="filter_due_to"
                        value="{{ $filters['filter_due_to'] }}"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                    />

                    <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition">Filter</button>

                    @if(!empty($activeFilters))
                        <a href="{{ route('student.dashboard') }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition text-center">Clear</a>
                    @endif
                </form>

                <form method="POST" action="{{ route('student.modules.records.store', $activeFilters) }}" class="mb-6 rounded-3xl bg-slate-50 p-5 border border-slate-100">
                    @csrf

                    @if($editRecord)
                        <input type="hidden" name="record_id" value="{{ $editRecord->id }}">
                    @endif

                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">Manage Module</h3>
                        @if($editRecord)
                            <a href="{{ route('student.dashboard', $activeFilters) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel Edit</a>
                        @endif
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <input
                            type="text"
                            name="module_name"
                            value="{{ old('module_name', $editRecord?->module_name) }}"
                            placeholder="Module name"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                            required
                        />

                        <input
                            type="text"
                            name="module_code"
                            value="{{ old('module_code', $editRecord?->module_code) }}"
                            placeholder="Module code"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                            required
                        />

                        <input
                            type="text"
                            name="instructor"
                            value="{{ old('instructor', $editRecord?->instructor) }}"
                            placeholder="Instructor"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <input
                            type="text"
                            name="schedule"
                            value="{{ old('schedule', $editRecord?->schedule) }}"
                            placeholder="Schedule"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <input
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            name="grade_percent"
                            value="{{ old('grade_percent', $editRecord?->grade_percent) }}"
                            placeholder="Grade %"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <input
                            type="number"
                            min="0"
                            name="documents_count"
                            value="{{ old('documents_count', $editRecord?->documents_count) }}"
                            placeholder="Documents count"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <input
                            type="text"
                            name="upcoming_assessment_title"
                            value="{{ old('upcoming_assessment_title', $editRecord?->upcoming_assessment_title) }}"
                            placeholder="Upcoming assessment title"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <input
                            type="number"
                            min="0"
                            name="upcoming_assessment_points"
                            value="{{ old('upcoming_assessment_points', $editRecord?->upcoming_assessment_points) }}"
                            placeholder="Assessment points"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <input
                            type="date"
                            name="upcoming_assessment_due_date"
                            value="{{ old('upcoming_assessment_due_date', $editRecord?->upcoming_assessment_due_date?->format('Y-m-d')) }}"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <input
                            type="number"
                            min="1"
                            name="upcoming_assessment_duration_minutes"
                            value="{{ old('upcoming_assessment_duration_minutes', $editRecord?->upcoming_assessment_duration_minutes) }}"
                            placeholder="Duration (minutes)"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />
                    </div>

                    <button type="submit" class="mt-4 rounded-2xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700 transition">
                        {{ $editRecord ? 'Update Module Record' : 'Add Module Record' }}
                    </button>
                </form>

                <div class="space-y-4">
                    @forelse($courses as $course)
                        <article class="group rounded-3xl bg-slate-50 p-5 border border-slate-100 hover:border-green-400 hover:bg-green-50/30 transition-all">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900 group-hover:text-green-700 transition-colors">{{ $course['name'] }}</h4>
                                    <p class="text-sm text-slate-500 mt-1">{{ $course['instructor'] }}</p>
                                </div>
                                <span class="inline-flex items-center justify-center rounded-full bg-slate-200 px-3 py-1 font-semibold text-slate-700 text-xs">
                                    {{ $course['code'] }}
                                </span>
                            </div>
                            <div class="mt-4 flex items-center gap-2 text-sm text-slate-600">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $course['schedule'] }}
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <a href="{{ route('student.dashboard', array_merge(['edit' => $course['id']], $activeFilters)) }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">Edit</a>

                                <form method="POST" action="{{ route('student.modules.records.destroy', array_merge(['moduleRecord' => $course['id']], $activeFilters)) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this module record?')" class="rounded-xl border border-rose-300 bg-white px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">Delete</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <article class="rounded-3xl bg-slate-50 p-5 border border-slate-100">
                            <p class="text-sm font-medium text-slate-700">No enrolled courses yet.</p>
                            <p class="mt-1 text-sm text-slate-500">Your courses will appear here once records are available.</p>
                        </article>
                    @endforelse
                </div>
            </section>

            <!-- Upcoming Assessments -->
            <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Upcoming Assessments</h2>
                        <p class="text-sm text-slate-500 mt-1">Plan ahead for your next quizzes.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($assessments as $assessment)
                        <article class="group rounded-3xl bg-slate-50 p-5 border border-slate-100 hover:border-green-400 hover:bg-green-50/30 transition-all">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900 group-hover:text-green-700 transition-colors">{{ $assessment['title'] }}</h4>
                                    <p class="text-sm text-slate-500 mt-1">{{ $assessment['course'] }}</p>
                                </div>
                                <span class="inline-flex items-center justify-center whitespace-nowrap rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                    {{ $assessment['points'] }}
                                </span>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600 border-t border-slate-200 pt-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Due: {{ $assessment['due'] }}
                                </div>
                                <div class="flex items-center gap-2 font-medium">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $assessment['duration'] }}
                                </div>
                            </div>
                        </article>
                    @empty
                        <article class="rounded-3xl bg-slate-50 p-5 border border-slate-100">
                            <p class="text-sm font-medium text-slate-700">No upcoming assessments.</p>
                            <p class="mt-1 text-sm text-slate-500">You are all caught up for now.</p>
                        </article>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
