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
                    <input
                        type="text"
                        name="student_name"
                        value="{{ old('student_name') }}"
                        placeholder="Student name"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        required
                    />
                    <input
                        type="text"
                        name="student_class"
                        value="{{ old('student_class') }}"
                        placeholder="Class (e.g. 10th A)"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                        required
                    />
                    <input
                        type="date"
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
                    <button type="submit" class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                        Register Record
                    </button>
                </form>

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
                        <a href="{{ route('faculty.grades.export.csv', ['grade_subject' => $gradeSubjectFilter]) }}" class="rounded-3xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition text-center">Export CSV</a>
                    </form>
                </div>

                <form method="POST" action="{{ route('grades.save') }}">
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
                                    <th class="px-4 py-4 font-semibold text-slate-500">Quiz (30%)</th>
                                    <th class="px-4 py-4 font-semibold text-slate-500">Assignment (30%)</th>
                                    <th class="px-4 py-4 font-semibold text-slate-500">Exam (40%)</th>
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
                                        <td class="px-4 py-4">
                                            <input type="number" step="0.01" min="0" max="100" name="grades[{{ $index }}][quiz]" value="{{ $gradeRecord['quiz'] }}" class="grade-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-slate-900 focus:outline-none w-24">
                                        </td>
                                        <td class="px-4 py-4">
                                            <input type="number" step="0.01" min="0" max="100" name="grades[{{ $index }}][assignment]" value="{{ $gradeRecord['assignment'] }}" class="grade-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-slate-900 focus:outline-none w-24">
                                        </td>
                                        <td class="px-4 py-4">
                                            <input type="number" step="0.01" min="0" max="100" name="grades[{{ $index }}][exam]" value="{{ $gradeRecord['exam'] }}" class="grade-input rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-slate-900 focus:outline-none w-24">
                                        </td>
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

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const table = document.getElementById('grades-table');
                    if (!table) return;

                    const rows = table.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        const inputs = row.querySelectorAll('.grade-input');
                        const avgDisplay = row.querySelector('.average-display');
                        const remarksDisplay = row.querySelector('.remarks-display');

                        if (inputs.length === 0) return;

                        const calculate = () => {
                            const quiz = parseFloat(inputs[0].value) || 0;
                            const assignment = parseFloat(inputs[1].value) || 0;
                            const exam = parseFloat(inputs[2].value) || 0;

                            const average = (quiz * 0.3) + (assignment * 0.3) + (exam * 0.4);
                            avgDisplay.textContent = average.toFixed(2);

                            if (average >= 75) {
                                remarksDisplay.textContent = 'Pass';
                                remarksDisplay.className = 'remarks-display inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700';
                            } else {
                                remarksDisplay.textContent = 'Fail';
                                remarksDisplay.className = 'remarks-display inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-rose-100 text-rose-700';
                            }
                        };

                        inputs.forEach(input => {
                            input.addEventListener('input', calculate);
                        });
                    });
                });
            </script>
        @endif
    </div>
@endsection