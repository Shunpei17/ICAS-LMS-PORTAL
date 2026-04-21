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

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Attendance Records</h2>
                    <p class="mt-2 text-sm text-slate-500">View recent student attendance updates.</p>
                </div>
                <form method="GET" action="{{ route('faculty.grades') }}" class="grid w-full gap-3 md:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_1fr_1fr_auto_auto_auto] xl:items-center">
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
                        name="from_date"
                        value="{{ $filters['from_date'] }}"
                        class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                    />

                    <input
                        type="date"
                        name="to_date"
                        value="{{ $filters['to_date'] }}"
                        class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                    />

                    <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition">Filter</button>
                    @if(!empty($activeFilters))
                        <a href="{{ route('faculty.grades') }}" class="rounded-3xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition text-center">Clear</a>
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
    </div>
@endsection