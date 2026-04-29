@extends('layouts.admin')

@section('title', 'Manage Enrollment per Subject')
@section('pageDescription', 'Review and manage student subject enrollment requests per academic level and course.')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
            </div>
        @endif

        {{-- Real-Time Analytics: Students per Academic Level & Course --}}
        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Student Analytics</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Real-time breakdown by academic level and course.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($levelStats as $stat)
                    @php
                        $colors = match(true) {
                            str_contains($stat['label'], 'Senior') => ['bg' => 'bg-violet-50', 'border' => 'border-violet-200', 'val' => 'text-violet-700', 'dot' => 'bg-violet-500'],
                            str_contains($stat['label'], '1st')    => ['bg' => 'bg-sky-50',    'border' => 'border-sky-200',    'val' => 'text-sky-700',    'dot' => 'bg-sky-500'],
                            str_contains($stat['label'], '2nd')    => ['bg' => 'bg-amber-50',  'border' => 'border-amber-200',  'val' => 'text-amber-700',  'dot' => 'bg-amber-500'],
                            default                                 => ['bg' => 'bg-emerald-50','border' => 'border-emerald-200','val' => 'text-emerald-700','dot' => 'bg-emerald-500'],
                        };
                    @endphp
                    <div class="rounded-2xl {{ $colors['bg'] }} border {{ $colors['border'] }} p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full {{ $colors['dot'] }}"></span>
                            <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">{{ $stat['label'] }}</p>
                        </div>
                        <p class="text-3xl font-black {{ $colors['val'] }}">{{ $stat['count'] }}</p>
                        <p class="text-xs text-slate-500 mt-1">students</p>
                    </div>
                @endforeach
            </div>

            {{-- Course Breakdown --}}
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach($courseStats as $cs)
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $cs['label'] }}</p>
                            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $cs['count'] }}</p>
                        </div>
                        <span class="rounded-xl bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">{{ $cs['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach($summary as $item)
                @php
                    $colors = match($item['color']) {
                        'amber'   => ['border' => 'border-amber-200',   'bg' => 'bg-amber-50',   'val' => 'text-amber-700',   'icon' => 'bg-amber-100 text-amber-600'],
                        'emerald' => ['border' => 'border-emerald-200', 'bg' => 'bg-emerald-50', 'val' => 'text-emerald-700', 'icon' => 'bg-emerald-100 text-emerald-600'],
                        'rose'    => ['border' => 'border-rose-200',    'bg' => 'bg-rose-50',    'val' => 'text-rose-700',    'icon' => 'bg-rose-100 text-rose-600'],
                        default   => ['border' => 'border-slate-200',   'bg' => 'bg-white',      'val' => 'text-slate-900',   'icon' => 'bg-slate-100 text-slate-600'],
                    };
                    $isActive = $tab === $item['tab'];
                @endphp
                <a href="{{ route('admin.enrollments', ['tab' => $item['tab']]) }}"
                   class="rounded-3xl {{ $colors['bg'] }} border-2 {{ $isActive ? $colors['border'] : 'border-transparent' }} p-6 shadow-sm hover:shadow-md transition-all block">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">{{ $item['label'] }}</p>
                    <div class="mt-3 flex items-center justify-between gap-4">
                        <p class="text-4xl font-bold {{ $colors['val'] }}">{{ $item['value'] }}</p>
                        @if($isActive)
                            <span class="text-xs font-semibold {{ $colors['val'] }} {{ $colors['icon'] }} rounded-full px-3 py-1">Active</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Filters & Table --}}
        <section class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        @if($tab === 'pending') Pending Enrollments
                        @elseif($tab === 'enrolled') Enrolled Students
                        @else Dropped Enrollments
                        @endif
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        @if($tab === 'pending') Review and approve incoming enrollment requests.
                        @elseif($tab === 'enrolled') View enrolled students and encode course details.
                        @else View dropped enrollment records.
                        @endif
                    </p>
                </div>

                {{-- Tab Switcher --}}
                <div class="flex rounded-2xl border border-slate-200 overflow-hidden text-sm font-semibold">
                    <a href="{{ route('admin.enrollments', ['tab' => 'pending']) }}"
                       class="px-4 py-2 transition {{ $tab === 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }}">Pending</a>
                    <a href="{{ route('admin.enrollments', ['tab' => 'enrolled']) }}"
                       class="px-4 py-2 transition {{ $tab === 'enrolled' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }}">Enrolled</a>
                    <a href="{{ route('admin.enrollments', ['tab' => 'dropped']) }}"
                       class="px-4 py-2 transition {{ $tab === 'dropped' ? 'bg-rose-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }}">Dropped</a>
                </div>
            </div>

            {{-- Course Filter --}}
            @if(count($courseOptions) > 0)
                <form method="GET" action="{{ route('admin.enrollments') }}" class="mb-6 flex flex-wrap gap-3 items-center">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <select name="course" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                        <option value="">All Courses</option>
                        @foreach($courseOptions as $opt)
                            <option value="{{ $opt['code'] }}" {{ $courseFilter === $opt['code'] ? 'selected' : '' }}>
                                {{ $opt['name'] }} ({{ $opt['code'] }})
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">Filter</button>
                    @if($courseFilter)
                        <a href="{{ route('admin.enrollments', ['tab' => $tab]) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Clear</a>
                    @endif
                </form>
            @endif

            {{-- Enrollments List --}}
            <div class="space-y-4">
                @forelse($enrollments as $enrollment)
                    @php
                        $st = $enrollment->enrollment_status;
                        $badge = match($st) {
                            'enrolled' => 'bg-emerald-100 text-emerald-700',
                            'dropped'  => 'bg-rose-100 text-rose-700',
                            default    => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5" x-data="{ showEncode: false }">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            {{-- Student Info --}}
                            <div class="flex items-start gap-4">
                                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-green-600 grid place-items-center text-white text-sm font-bold">
                                    {{ strtoupper(substr($enrollment->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $enrollment->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-slate-500">{{ $enrollment->user->email ?? '' }}</p>
                                    {{-- Course & Year Level --}}
                                    @if($enrollment->user?->course || $enrollment->user?->academic_level)
                                        <p class="mt-0.5 text-xs text-slate-600">
                                            @if($enrollment->user?->course)
                                                <span class="inline-flex rounded bg-green-100 text-green-700 px-1.5 py-0.5 font-semibold">{{ $enrollment->user->course }}</span>
                                            @endif
                                            @if($enrollment->user?->academic_level)
                                                <span class="ml-1 text-slate-500">{{ $enrollment->user->academic_level }}</span>
                                            @endif
                                        </p>
                                    @endif
                                    <p class="mt-1 text-xs text-slate-600">
                                        <span class="font-semibold">{{ $enrollment->module_name }}</span>
                                        <span class="text-slate-400 mx-1">·</span>
                                        <span class="font-mono">{{ $enrollment->module_code }}</span>
                                    </p>
                                    @if($enrollment->instructor)
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $enrollment->instructor }}</p>
                                    @endif
                                    <p class="text-xs text-slate-400 mt-1">Applied {{ $enrollment->created_at?->diffForHumans() }}</p>
                                </div>
                            </div>

                            {{-- Status & Actions --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full {{ $badge }} px-3 py-1 text-xs font-bold capitalize">{{ ucfirst($st) }}</span>

                                @if($tab === 'pending')
                                    <form method="POST" action="{{ route('admin.enrollments.approve', $enrollment) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                            ✓ Approve
                                        </button>
                                    </form>
                                @endif

                                @if($tab === 'enrolled')
                                    <button @click="showEncode = !showEncode"
                                            class="rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                        ✏️ Encode Course
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Encode Course Form (enrolled tab only) --}}
                        @if($tab === 'enrolled')
                            <div x-show="showEncode" x-cloak class="mt-5 rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-bold text-slate-700 mb-3 uppercase tracking-wide">Encode Course Details</p>
                                <form method="POST" action="{{ route('admin.enrollments.encode', $enrollment) }}" class="grid gap-3 sm:grid-cols-2">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Course Name</label>
                                        <input type="text" name="module_name" value="{{ $enrollment->module_name }}" required
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Course Code</label>
                                        <input type="text" name="module_code" value="{{ $enrollment->module_code }}" required
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-400">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Instructor</label>
                                        <input type="text" name="instructor" value="{{ $enrollment->instructor }}"
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Schedule</label>
                                        <input type="text" name="schedule" value="{{ $enrollment->schedule }}"
                                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                    </div>
                                    <div class="sm:col-span-2 flex gap-2">
                                        <button type="submit" class="rounded-xl bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-700 transition">Save Changes</button>
                                        <button type="button" @click="showEncode = false" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                        <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <p class="text-sm font-medium text-slate-700">No {{ $tab }} enrollments found.</p>
                        @if($courseFilter)
                            <p class="mt-1 text-xs text-slate-400">Try clearing the course filter.</p>
                        @endif
                    </div>
                @endforelse

                <div class="mt-4">{{ $enrollments->links() }}</div>
            </div>
        </section>
    </div>
@endsection