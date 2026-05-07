@extends('layouts.admin')
@section('title', 'System Settings')
@section('pageDescription', 'Configure school information, academic term, and platform settings.')
@section('content')
<div class="space-y-6" x-data="{ tab: 'general' }">
    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-2 flex gap-2 flex-wrap">
        @foreach(['general'=>'General','academic'=>'Academic Term','grading'=>'Grading','appearance'=>'Appearance'] as $k=>$l)
            <button @click="tab='{{ $k }}'" :class="tab==='{{ $k }}'?'bg-green-600 text-white shadow-sm':'text-slate-600 hover:bg-slate-100'" class="rounded-2xl px-5 py-2.5 text-sm font-semibold transition">{{ $l }}</button>
        @endforeach
    </section>

    {{-- General --}}
    <div x-show="tab==='general'" x-cloak>
        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-5">School Information</h3>
            <form class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">School / Institution Name</label>
                        <input type="text" value="{{ $schoolSettings['school_name'] }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">School Code</label>
                        <input type="text" value="{{ $schoolSettings['school_code'] }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Timezone</label>
                        <select class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                            <option selected>Asia/Manila (UTC+8)</option>
                            <option>UTC</option>
                        </select>
                    </div>
                </div>
                <button class="rounded-2xl bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700 transition">Save Changes</button>
            </form>
        </section>
    </div>

    {{-- Academic Term --}}
    <div x-show="tab==='academic'" x-cloak>
        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-5">Academic Term Settings</h3>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Academic Year</label>
                        <input name="academic_year" type="text" value="{{ $schoolSettings['academic_year'] }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Current Semester</label>
                        <select name="current_semester" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                            <option value="First Semester" @selected($schoolSettings['semester']==='First Semester')>First Semester</option>
                            <option value="Second Semester" @selected($schoolSettings['semester']==='Second Semester')>Second Semester</option>
                            <option value="Third Semester" @selected($schoolSettings['semester']==='Third Semester')>Third Semester</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Enrollment Start</label>
                        <input name="enrollment_start" type="date" value="{{ $schoolSettings['enrollment_start'] }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Enrollment End</label>
                        <input name="enrollment_end" type="date" value="{{ $schoolSettings['enrollment_end'] }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Final Exam Start Date</label>
                        <input name="final_exam_start" type="date" value="{{ $schoolSettings['exam_start'] }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>
                </div>
                <button type="submit" class="rounded-2xl bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700 transition">Save Term Settings</button>
            </form>
        </section>
    </div>

    {{-- Grading --}}
    <div x-show="tab==='grading'" x-cloak>
        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Grading Standards</h3>
            <p class="text-sm text-slate-500 mb-6">These are the school-wide grading standards. Faculty members define their own criteria per classroom.</p>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    {{-- Static Passing Grade --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Passing Grade (%)</label>
                        <div class="relative">
                            <input type="number" value="75" readonly disabled
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-600 cursor-not-allowed">
                            <div class="absolute inset-y-0 right-4 flex items-center">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-400">Locked — 75% is the institutional standard. Students scoring below this are flagged as "Failed".</p>
                    </div>

                    {{-- Grading Scale --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Grading Scale</label>
                        <input type="hidden" name="grading_scale" value="gpa">
                        <div class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">Static GPA (Primary)</div>
                        <p class="mt-1.5 text-xs text-slate-400">The GPA scale is used to convert percentage grades across all portals.</p>
                    </div>
                </div>

                {{-- Grade Equivalency Table --}}
                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-8 w-8 rounded-xl bg-green-100 text-green-600 grid place-items-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-700">Grade Equivalency (GPA)</p>
                            <p class="text-xs text-slate-400">This table is used globally by Faculty for auto-computation of GPA grades.</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="text-sm min-w-full">
                            <thead>
                                <tr class="text-slate-500 text-xs uppercase border-b border-slate-200">
                                    <th class="py-2.5 pr-6 text-left font-semibold">GPA</th>
                                    <th class="py-2.5 pr-6 text-left font-semibold">Percent Range</th>
                                    <th class="py-2.5 text-left font-semibold">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($schoolSettings['grade_equivalency'] as $row)
                                    @php
                                        $isDropped = $row['gpa'] === 'Dropped';
                                        $isPassing = !$isDropped;
                                        // Extract the lower bound from the range string
                                        $rangeParts = explode('-', $row['range']);
                                        $lowerBound = (int) trim($rangeParts[0]);
                                        $passFail = $lowerBound >= 75 ? 'Passed' : ($isDropped ? 'Dropped' : 'Failed');
                                        $rowColor = match($passFail) {
                                            'Passed' => 'text-emerald-700',
                                            'Dropped' => 'text-slate-400',
                                            default => 'text-rose-600',
                                        };
                                    @endphp
                                    <tr class="hover:bg-white transition-colors">
                                        <td class="py-2.5 pr-6 font-bold text-slate-900">{{ $row['gpa'] }}</td>
                                        <td class="py-2.5 pr-6 text-slate-600">{{ $row['range'] }}</td>
                                        <td class="py-2.5">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold {{ $passFail === 'Passed' ? 'bg-emerald-100 text-emerald-700' : ($passFail === 'Dropped' ? 'bg-slate-100 text-slate-400' : 'bg-rose-100 text-rose-600') }}">
                                                {{ $passFail }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Info Card --}}
                <div class="rounded-2xl bg-sky-50 border border-sky-200 p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-sky-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="text-sm font-semibold text-sky-800">Grading Criteria</p>
                        <p class="text-xs text-sky-700 mt-0.5">
                            Grading criteria (Quizzes, Exams, Assignments, etc.) are now managed by <strong>Faculty</strong> on a per-classroom basis.
                            Faculty can configure their own component weights in the <strong>Grade Management → Grades</strong> tab.
                        </p>
                    </div>
                </div>

                <button type="submit" class="rounded-2xl bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700 transition">Save Grading Settings</button>
            </form>
        </section>
    </div>

    {{-- Appearance --}}
    <div x-show="tab==='appearance'" x-cloak>
        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-5">Appearance</h3>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
                @csrf
                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                    <p class="font-semibold text-slate-900 text-sm mb-1">Portal Color Theme</p>
                    <p class="text-xs text-slate-500 mb-3">Change the primary color for each portal.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold">Admin Portal Primary</label>
                            <input type="color" name="theme_admin_color" value="{{ $schoolSettings['theme_admin_color'] }}" class="w-full h-10 rounded-md border">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Faculty Portal Primary</label>
                            <input type="color" name="theme_faculty_color" value="{{ $schoolSettings['theme_faculty_color'] }}" class="w-full h-10 rounded-md border">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Student Portal Primary</label>
                            <input type="color" name="theme_student_color" value="{{ $schoolSettings['theme_student_color'] }}" class="w-full h-10 rounded-md border">
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3.5">
                    <div>
                        <p class="font-semibold text-slate-900 text-sm">Compact Sidebar</p>
                        <p class="text-xs text-slate-500 mt-0.5">Show only icons in the sidebar for more content space.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="compact_sidebar" class="sr-only peer" {{ old('compact_sidebar', false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition peer-checked:bg-green-600"></div>
                    </label>
                </div>
                <div class="mt-5">
                    <button type="submit" class="rounded-2xl bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700 transition">Save Appearance</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
