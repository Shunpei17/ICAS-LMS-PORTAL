@extends('layouts.admin')

@section('title', 'Attendance Monitoring')
@section('pageDescription', 'Monitor student attendance records across all faculty classes.')

@section('content')
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach($summary as $item)
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-4 text-3xl font-bold text-slate-900">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Student Attendance Records</h2>
                    <p class="mt-2 text-sm text-slate-500">Track attendance activity and monitor trends by class, date range, and faculty.</p>
                </div>

                <form method="GET" action="{{ route('admin.attendance') }}" class="grid w-full gap-3 md:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_1fr_1fr_auto_auto] xl:items-center">
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Search student..."
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

                    <select name="faculty_user_id" class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none">
                        <option value="" @selected($filters['faculty_user_id'] === '')>All Faculty</option>
                        @foreach($facultyOptions as $facultyOption)
                            <option value="{{ $facultyOption['id'] }}" @selected($filters['faculty_user_id'] === (string) $facultyOption['id'])>
                                {{ $facultyOption['name'] }}
                            </option>
                        @endforeach
                    </select>

                    <input
                        type="date"
                        name="from_date"
                        value="{{ $filters['from_date'] }}"
                        class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700 focus:border-slate-900 focus:outline-none"
                    />

                    <button type="submit" class="rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Filter</button>
                    @if(!empty($activeFilters))
                        <a href="{{ route('admin.attendance') }}" class="rounded-3xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Clear</a>
                    @endif
                </form>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-4 font-semibold text-slate-500">Student</th>
                            <th class="px-4 py-4 font-semibold text-slate-500">Class</th>
                            <th class="px-4 py-4 font-semibold text-slate-500">Faculty</th>
                            <th class="px-4 py-4 font-semibold text-slate-500">Date</th>
                            <th class="px-4 py-4 font-semibold text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($records as $record)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-slate-100 grid place-items-center text-sm font-semibold text-slate-700">{{ strtoupper(substr($record->student_name, 0, 1)) }}</div>
                                        <span class="font-medium text-slate-900">{{ $record->student_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">{{ $record->student_class }}</td>
                                <td class="px-4 py-4">{{ $record->faculty?->name ?? 'Unknown Faculty' }}</td>
                                <td class="px-4 py-4">{{ $record->attendance_date?->format('n/j/Y') ?? '-' }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $record->status === 'Present' ? 'bg-emerald-100 text-emerald-700' : ($record->status === 'Late' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">{{ $record->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                    No attendance records found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="mt-6">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
