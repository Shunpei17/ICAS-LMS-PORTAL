@extends('layouts.faculty')

@section('title', 'Grade Management')
@section('pageDescription', 'Track and manage student attendance')

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

        <div class="grid gap-4 md:grid-cols-4">
            @foreach($summary as $item)
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">{{ $item['label'] }}</p>
                    <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="flex gap-8 border-b border-slate-200">
            <a href="{{ route('faculty.grades', ['tab' => 'attendance']) }}" class="pb-4 text-sm font-semibold transition-colors {{ $tab === 'attendance' ? 'text-slate-900 border-b-2 border-slate-900' : 'text-slate-500 hover:text-slate-700' }}">Attendance Records</a>
            <a href="{{ route('faculty.grades', ['tab' => 'grades']) }}" class="pb-4 text-sm font-semibold transition-colors {{ $tab === 'grades' ? 'text-slate-900 border-b-2 border-slate-900' : 'text-slate-500 hover:text-slate-700' }}">Grades</a>
        </div>

        @if($tab === 'attendance')
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Attendance Records</h2>
                        <p class="mt-2 text-sm text-slate-500">View recent student attendance updates.</p>
                    </div>
                    <form method="GET" action="{{ route('faculty.grades') }}" class="flex flex-wrap items-center gap-3 w-full lg:w-auto lg:justify-end">
                        <input type="hidden" name="tab" value="attendance">
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] }}"
                            placeholder="Search students..."
                            class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <select name="status" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none">
                            <option value="" @selected($filters['status'] === '')>All Statuses</option>
                            <option value="Present" @selected($filters['status'] === 'Present')>Present</option>
                            <option value="Absent" @selected($filters['status'] === 'Absent')>Absent</option>
                            <option value="Late" @selected($filters['status'] === 'Late')>Late</option>
                        </select>

                        <select name="student_class" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none">
                            <option value="" @selected($filters['student_class'] === '')>All Classes</option>
                            @foreach($classOptions as $classOption)
                                <option value="{{ $classOption }}" @selected($filters['student_class'] === $classOption)>{{ $classOption }}</option>
                            @endforeach
                        </select>

                        <input
                            type="date"
                            name="date"
                            value="{{ $filters['date'] }}"
                            class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        />

                        <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition">Filter</button>
                        @if(!empty($activeFilters))
                            <a href="{{ route('faculty.grades', ['tab' => 'attendance']) }}" class="rounded-3xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition text-center">Clear</a>
                        @endif
                        <a href="{{ route('faculty.grades.export', $activeFilters) }}" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition text-center">Export CSV</a>
                    </form>
                </div>

                <form method="POST" action="{{ route('faculty.grades.records.store') }}" class="mt-6 grid gap-3 md:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
                    @csrf

                    <!-- Alert for existing attendance -->
                    <div id="attendance-alert" class="hidden col-span-full rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <div>
                            <strong>Attendance exists for this date:</strong> 
                            <span id="attendance-count">Loading...</span> record(s) found.
                            <div id="existing-records-summary" class="mt-2 text-xs"></div>
                        </div>
                    </div>

                    <input
                        type="text"
                        name="student_name"
                        value="{{ old('student_name') }}"
                        placeholder="Student name"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        required
                    />
                    <select
                        name="student_class"
                        id="student-class-input"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        required
                    >
                        <option value="">Select Subject/Class</option>
                        @foreach($facultyClassrooms as $classroom)
                            <option value="{{ $classroom->code }}" @selected(old('student_class') === $classroom->code)>{{ $classroom->name }} ({{ $classroom->code }})</option>
                        @endforeach
                    </select>
                    <input
                        type="date"
                        id="attendance-date-input"
                        name="attendance_date"
                        value="{{ old('attendance_date', now()->toDateString()) }}"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        required
                    />
                    <select
                        name="status"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        required
                    >
                        <option value="Present" @selected(old('status') === 'Present')>Present</option>
                        <option value="Absent" @selected(old('status') === 'Absent')>Absent</option>
                        <option value="Late" @selected(old('status') === 'Late')>Late</option>
                    </select>
                    <label class="inline-flex items-center mr-3">
                        <input type="checkbox" name="update_if_exists" value="1" class="mr-2" />
                        <span class="text-sm text-slate-700">Update existing if found</span>
                    </label>

                    <button type="button" id="load-attendance-btn" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        Load Today
                    </button>

                    <button type="submit" class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                        Register Record
                    </button>
                </form>

                <script>
                    document.getElementById('load-attendance-btn').addEventListener('click', async function() {
                        const studentClass = document.getElementById('student-class-input').value;
                        const attendanceDate = document.getElementById('attendance-date-input').value;

                        if (!studentClass || !attendanceDate) {
                            alert('Please enter Class and Date first.');
                            return;
                        }

                        try {
                            const response = await fetch("{{ route('faculty.grades.load-today-attendance') }}", {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    student_class: studentClass,
                                    attendance_date: attendanceDate,
                                }),
                            });

                            // Actually, GET with body is not standard. Use query params instead
                            const url = new URL("{{ route('faculty.grades.load-today-attendance') }}", window.location.origin);
                            url.searchParams.append('student_class', studentClass);
                            url.searchParams.append('attendance_date', attendanceDate);

                            const result = await fetch(url.toString(), {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            }).then(r => r.json());

                            const alert = document.getElementById('attendance-alert');
                            const summary = document.getElementById('existing-records-summary');

                            if (result.exists) {
                                alert.classList.remove('hidden');
                                document.getElementById('attendance-count').textContent = result.count;
                                summary.innerHTML = result.records.map(r => 
                                    `<div class="mt-1">• ${r.student_name}: ${r.status}</div>`
                                ).join('');
                            } else {
                                alert.classList.add('hidden');
                            }
                        } catch (error) {
                            console.error('Error loading attendance:', error);
                            alert('Error loading attendance records.');
                        }
                    });
                </script>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-4 font-semibold text-slate-500">Student Name</th>
                                <th class="px-4 py-4 font-semibold text-slate-500">Class</th>
                                <th class="px-4 py-4 font-semibold text-slate-500">Date</th>
                                <th class="px-4 py-4 font-semibold text-slate-500">Status</th>
                                <th class="px-4 py-4 font-semibold text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($records as $record)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-slate-100 grid place-items-center text-sm font-semibold text-slate-700">{{ $record['initials'] }}</div>
                                            <span class="font-medium text-slate-900">{{ $record['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">{{ $record['class'] }}</td>
                                    <td class="px-4 py-4">{{ $record['date'] }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $record['status'] === 'Present' ? 'bg-emerald-100 text-emerald-700' : ($record['status'] === 'Late' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">{{ $record['status'] }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <form method="POST" action="{{ route('faculty.grades.records.update', array_merge(['attendanceRecord' => $record['id']], $activeFilters)) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 focus:border-slate-900 focus:outline-none">
                                                <option value="Present" @selected($record['status'] === 'Present')>Present</option>
                                                <option value="Absent" @selected($record['status'] === 'Absent')>Absent</option>
                                                <option value="Late" @selected($record['status'] === 'Late')>Late</option>
                                            </select>
                                            <button type="submit" class="text-sm font-semibold text-slate-900 hover:text-slate-700">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                        No attendance records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Grade Records</h2>
                        <p class="mt-2 text-sm text-slate-500">Manage student grades and activities.</p>
                    </div>
                    <form method="GET" action="{{ route('faculty.grades') }}" class="flex flex-wrap items-center gap-3 w-full lg:w-auto lg:justify-end">
                        <input type="hidden" name="tab" value="grades">
                        <input type="text" name="grade_search" value="{{ $gradeSearch }}" placeholder="Search students..." class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none" />
                        <select name="grade_subject" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none">
                            @foreach($gradeSubjects as $subjectOption)
                                <option value="{{ $subjectOption }}" @selected($gradeSubjectFilter === $subjectOption)>{{ $subjectOption }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition">Filter</button>
                        @php
                            try {
                                $exportCsvUrl = route('faculty.grades.export.csv', ['grade_subject' => $gradeSubjectFilter]);
                            } catch (\Exception $e) {
                                $exportCsvUrl = url('faculty/grades/export-grades') . ($gradeSubjectFilter ? '?grade_subject=' . urlencode($gradeSubjectFilter) : '');
                            }
                        @endphp
                        <a href="{{ $exportCsvUrl }}" class="rounded-3xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition text-center">Export CSV</a>
                    </form>
                </div>

                <form method="POST" action="{{ route('faculty.grades.save') }}">
                    @csrf
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition">Save Grades</button>
                    </div>
                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700" id="grades-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-4 font-semibold text-slate-500">Student Name</th>
                                    <th class="px-4 py-4 font-semibold text-slate-500">Subject</th>
                                    @if($activeCriteria->isNotEmpty())
                                        @foreach($activeCriteria as $criterion)
                                            <th class="px-4 py-4 font-semibold text-slate-500">{{ $criterion->component_name }} ({{ (float)$criterion->weight }}%)</th>
                                        @endforeach
                                    @else
                                        <th class="px-4 py-4 font-semibold text-slate-500">Quiz (30%)</th>
                                        <th class="px-4 py-4 font-semibold text-slate-500">Assignment (30%)</th>
                                        <th class="px-4 py-4 font-semibold text-slate-500">Exam (40%)</th>
                                    @endif
                                    <th class="px-4 py-4 font-semibold text-slate-500">Average</th>
                                    <th class="px-4 py-4 font-semibold text-slate-500">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse($studentsWithGrades as $index => $gradeRecord)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-4">
                                            <span class="font-medium text-slate-900">{{ $gradeRecord['student_name'] }}</span>
                                            <input type="hidden" name="grades[{{ $index }}][student_id]" value="{{ $gradeRecord['student_id'] }}">
                                        </td>
                                        <td class="px-4 py-4">
                                            {{ $gradeRecord['subject_id'] }}
                                            <input type="hidden" name="grades[{{ $index }}][subject_id]" value="{{ $gradeRecord['subject_id'] }}">
                                        </td>
                                        @if($activeCriteria->isNotEmpty())
                                            @foreach($activeCriteria as $criterion)
                                                @php
                                                    $compKey = strtolower(str_replace(' ', '_', $criterion->component_name));
                                                    $val = $gradeRecord['component_scores'][$compKey] ?? $gradeRecord[$compKey] ?? 0;
                                                @endphp
                                                <td class="px-4 py-4">
                                                    <input type="number" step="0.01" min="0" max="100" 
                                                           name="grades[{{ $index }}][components][{{ $compKey }}]" 
                                                           value="{{ $val }}" 
                                                           data-weight="{{ $criterion->weight }}"
                                                           class="grade-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-slate-900 focus:outline-none w-24">
                                                </td>
                                            @endforeach
                                        @else
                                            <td class="px-4 py-4">
                                                <input type="number" step="0.01" min="0" max="100" name="grades[{{ $index }}][quiz]" value="{{ $gradeRecord['quiz'] }}" data-weight="30" class="grade-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-slate-900 focus:outline-none w-24">
                                            </td>
                                            <td class="px-4 py-4">
                                                <input type="number" step="0.01" min="0" max="100" name="grades[{{ $index }}][assignment]" value="{{ $gradeRecord['assignment'] }}" data-weight="30" class="grade-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-slate-900 focus:outline-none w-24">
                                            </td>
                                            <td class="px-4 py-4">
                                                <input type="number" step="0.01" min="0" max="100" name="grades[{{ $index }}][exam]" value="{{ $gradeRecord['exam'] }}" data-weight="40" class="grade-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-slate-900 focus:outline-none w-24">
                                            </td>
                                        @endif
                                        <td class="px-4 py-4">
                                            <span class="average-display font-semibold">{{ $gradeRecord['average'] ?? '0.00' }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="remarks-display inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ ($gradeRecord['remarks'] ?? 'Fail') === 'Pass' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $gradeRecord['remarks'] ?? 'Fail' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                                            No students found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            {{-- Per-Classroom Grading Criteria Configuration --}}
            <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200 mt-6">
                <div class="flex items-center gap-3 mb-1">
                    <div class="h-9 w-9 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Grading Criteria Configuration</h3>
                        <p class="text-sm text-slate-500">Set up component weights for each of your classrooms. Total must equal 100%.</p>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl bg-sky-50 border border-sky-200 px-4 py-3 flex items-start gap-2 mb-6">
                    <svg class="w-4 h-4 text-sky-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs text-sky-700">The institutional passing grade is <strong>75%</strong> (set by Admin). Grades are auto-converted to GPA using the school's equivalency table.</p>
                </div>

                @if(isset($facultyClassrooms) && $facultyClassrooms->count() > 0)
                    <div class="space-y-5">
                        @foreach($facultyClassrooms as $classroom)
                            @php
                                $existingCriteria = $classroom->gradingCriteria->count() > 0
                                    ? $classroom->gradingCriteria->map(fn($c) => ['component_name' => $c->component_name, 'weight' => (float)$c->weight, 'term' => $c->term])->values()->all()
                                    : [
                                        ['component_name' => 'Quiz', 'weight' => 30, 'term' => 'Prelim'],
                                        ['component_name' => 'Assignment', 'weight' => 30, 'term' => 'Midterm'],
                                        ['component_name' => 'Exam', 'weight' => 40, 'term' => 'Final'],
                                    ];
                            @endphp
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5" x-data="{
                                criteria: @js($existingCriteria),
                                get totalWeight() { return this.criteria.reduce((s, c) => s + Number(c.weight || 0), 0); },
                                addRow() { this.criteria.push({ component_name: '', weight: 0, term: 'Prelim' }); },
                                removeRow(i) { this.criteria.splice(i, 1); }
                            }">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $classroom->name }}</p>
                                        <p class="text-xs font-mono text-slate-400">{{ $classroom->code }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full {{ $classroom->gradingCriteria->count() > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} px-3 py-1 text-xs font-bold">
                                        {{ $classroom->gradingCriteria->count() > 0 ? 'Configured' : 'Default' }}
                                    </span>
                                </div>

                                <form method="POST" action="{{ route('faculty.classrooms.grading-criteria.store', $classroom->id) }}">
                                    @csrf
                                    <div class="space-y-2">
                                        <template x-for="(item, idx) in criteria" :key="idx">
                                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                                                <input type="text" x-model="item.component_name" :name="'criteria['+idx+'][component_name]'" placeholder="Component name" required class="flex-1 min-w-[140px] rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                                <div class="relative w-24">
                                                    <input type="number" x-model.number="item.weight" :name="'criteria['+idx+'][weight]'" min="0" max="100" step="0.01" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 pr-7">
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 text-sm pointer-events-none">%</span>
                                                </div>
                                                <select x-model="item.term" :name="'criteria['+idx+'][term]'" required class="w-28 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                                    <option>Prelim</option><option>Midterm</option><option>Final</option>
                                                </select>
                                                <button type="button" @click="removeRow(idx)" class="text-rose-500 hover:bg-rose-50 p-1.5 rounded-lg transition" title="Remove">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between">
                                        <button type="button" @click="addRow" class="text-xs font-semibold bg-white border border-slate-200 text-slate-700 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition">+ Add Component</button>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold" :class="totalWeight === 100 ? 'text-emerald-600' : 'text-rose-600'">Total: <span x-text="totalWeight"></span>%</p>
                                            <p x-show="totalWeight !== 100" x-cloak class="text-xs text-rose-500 mt-0.5">Must equal 100% to save.</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-slate-200">
                                        <button type="submit" :disabled="totalWeight !== 100"
                                                :class="totalWeight === 100 ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                                class="rounded-xl px-5 py-2 text-sm font-semibold transition">Save Criteria</button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                        <p class="text-sm font-medium text-slate-600">No classrooms assigned yet.</p>
                        <p class="text-xs text-slate-400 mt-1">Create a classroom first, then configure grading criteria here.</p>
                    </div>
                @endif
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const table = document.getElementById('grades-table');
                    if (!table) return;
                    const PASSING_GRADE = 75;
                    table.querySelectorAll('tbody tr').forEach(row => {
                        const inputs = row.querySelectorAll('.grade-input');
                        const avgDisplay = row.querySelector('.average-display');
                        const remarksDisplay = row.querySelector('.remarks-display');
                        if (inputs.length === 0) return;
                        const calculate = () => {
                            let average = 0;
                            inputs.forEach(input => {
                                const val = parseFloat(input.value) || 0;
                                const weight = parseFloat(input.dataset.weight) || 0;
                                average += (val * (weight / 100));
                            });
                            avgDisplay.textContent = average.toFixed(2);
                            if (average >= PASSING_GRADE) {
                                remarksDisplay.textContent = 'Pass';
                                remarksDisplay.className = 'remarks-display inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700';
                            } else {
                                remarksDisplay.textContent = 'Fail';
                                remarksDisplay.className = 'remarks-display inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-rose-100 text-rose-700';
                            }
                        };
                        inputs.forEach(input => input.addEventListener('input', calculate));
                    });
                });
            </script>
        @endif
    </div>
@endsection