@extends('layouts.admin')

@section('title', 'Classroom — '.($classroom->name ?? 'Classroom'))
@section('pageDescription', 'Students enrolled in the classroom')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $classroom->name }} <span class="text-sm font-mono text-slate-400">({{ $classroom->code }})</span></h1>
                <p class="text-sm text-slate-500 mt-1">Assigned faculty: <strong>{{ $classroom->faculty?->name ?? 'Unassigned' }}</strong></p>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-sm text-slate-600">Total students: <span class="font-bold">{{ $total }}</span></div>

                <div class="relative inline-block text-left">
                    <button id="exportToggle" type="button" class="rounded-xl bg-slate-800 px-4 py-2 text-white text-sm">Export ▾</button>
                    <div id="exportMenu" class="hidden origin-top-right absolute right-0 mt-2 w-44 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <a href="{{ route('admin.classrooms.export', $classroom) }}?format=csv" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download CSV</a>
                            <a href="{{ route('admin.classrooms.export', $classroom) }}?format=xlsx" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download Excel</a>
                            <a href="{{ route('admin.classrooms.export', $classroom) }}?format=pdf" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download PDF</a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.classrooms') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600">Back</a>
            </div>
        </div>

        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
            <form method="GET" action="{{ route('admin.classrooms.show', $classroom) }}" class="mb-4 flex items-center gap-3">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search student number, name, or email…"
                       class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 w-72">
                <button class="rounded-xl bg-green-600 px-4 py-2 text-sm text-white">Search</button>
                @if($search)
                    <a href="{{ route('admin.classrooms.show', $classroom) }}" class="text-sm text-slate-500">Clear</a>
                @endif
            </form>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase">Student Number</th>
                            <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase">Full Name</th>
                            <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase">Academic Level</th>
                            <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase">Enrollment Status</th>
                            <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase">Email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $student)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-4 text-slate-700">{{ $student->student_number ?? '—' }}</td>
                                <td class="px-5 py-4 font-medium text-slate-900">{{ $student->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $student->academic_level ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize {{ ($student->pivot->enrollment_status ?? '') === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $student->pivot->enrollment_status ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $student->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $students->links() }}</div>
        </section>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const exportToggle = document.getElementById('exportToggle');
            const exportMenu = document.getElementById('exportMenu');
            if (exportToggle && exportMenu) {
                exportToggle.addEventListener('click', () => {
                    exportMenu.classList.toggle('hidden');
                });
                
                // Close when clicking outside
                document.addEventListener('click', (e) => {
                    if (!exportToggle.contains(e.target) && !exportMenu.contains(e.target)) {
                        exportMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endsection
