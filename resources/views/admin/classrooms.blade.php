@extends('layouts.admin')

@section('title', 'Classrooms')
@section('pageDescription', 'Review all classrooms, faculty assignments, and academic performance metrics.')

@section('content')
    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach($summary as $item)
                @php
                    $colors = match($item['color']) {
                        'emerald' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'val' => 'text-emerald-700', 'icon' => 'bg-emerald-100 text-emerald-600'],
                        'sky'     => ['bg' => 'bg-sky-50',     'border' => 'border-sky-200',     'val' => 'text-sky-700',     'icon' => 'bg-sky-100 text-sky-600'],
                        default   => ['bg' => 'bg-white',      'border' => 'border-slate-200',   'val' => 'text-slate-900',   'icon' => 'bg-slate-100 text-slate-600'],
                    };
                @endphp
                <div class="rounded-3xl {{ $colors['bg'] }} border {{ $colors['border'] }} p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-3 text-4xl font-bold {{ $colors['val'] }}">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Search & Filter --}}
        <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">All Classrooms</h2>
                </div>

                <form method="GET" action="{{ route('admin.classrooms') }}" class="flex flex-wrap gap-3 items-center">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name or code…"
                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 w-52 focus:outline-none focus:ring-2 focus:ring-green-400">
                    <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-400">
                        <option value="">All Statuses</option>
                        <option value="active"   {{ $statusFilter === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit" class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">Filter</button>
                    @if($search || $statusFilter)
                        <a href="{{ route('admin.classrooms') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Clear</a>
                    @endif
                    <a href="{{ route('admin.classrooms.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add a Classroom
                    </a>
                </form>
            </div>

            {{-- Table --}}
            @if(count($classrooms) > 0)
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Classroom</th>
                                <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Faculty</th>
                                <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Schedule</th>
                                <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Students</th>
                                <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Avg Grade</th>
                                <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Attendance</th>
                                <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($classrooms as $room)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-slate-900"><a href="{{ route('admin.classrooms.show', $room['id']) }}" class="hover:underline">{{ $room['name'] }}</a></p>
                                        <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $room['code'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="h-7 w-7 rounded-full bg-green-600 grid place-items-center text-white text-xs font-bold flex-shrink-0">
                                                {{ strtoupper(substr($room['faculty_name'], 0, 1)) }}
                                            </div>
                                            <span class="text-slate-700">{{ $room['faculty_name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $room['schedule'] ?? '—' }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                            {{ $room['student_count'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="font-bold {{ $room['avg_grade'] !== '—' ? 'text-emerald-600' : 'text-slate-300' }}">
                                            {{ $room['avg_grade'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="font-bold {{ $room['attendance_rate'] !== '—' ? 'text-sky-600' : 'text-slate-300' }}">
                                            {{ $room['attendance_rate'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize
                                            {{ $room['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ ucfirst($room['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <form action="{{ route('admin.classrooms.status', $room['id']) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-md px-3 py-1 text-xs font-semibold transition
                                                    {{ $room['status'] === 'active' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                                    {{ $room['status'] === 'active' ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <button data-id="{{ $room['id'] }}" data-name="{{ $room['name'] }}" class="assign-faculty rounded-md bg-sky-600 text-white px-3 py-1 text-xs font-semibold hover:bg-sky-700 transition">Assign</button>
                                            <div class="relative inline-block text-left">
                                                <button type="button" class="export-btn rounded-md bg-slate-800 text-white px-3 py-1 text-xs font-semibold hover:bg-slate-900 transition">Export ▾</button>
                                                <div class="export-menu hidden absolute right-0 mt-2 z-10 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                                    <div class="py-1">
                                                        <a href="{{ route('admin.classrooms.export', $room['id']) }}?format=csv" class="block px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">CSV</a>
                                                        <a href="{{ route('admin.classrooms.export', $room['id']) }}?format=xlsx" class="block px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">Excel</a>
                                                        <a href="{{ route('admin.classrooms.export', $room['id']) }}?format=pdf" class="block px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">PDF</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <form action="{{ route('admin.classrooms.destroy', $room['id']) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this classroom? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md bg-rose-50 p-1.5 text-rose-600 hover:bg-rose-100 transition" title="Delete Classroom">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
                    <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1z"></path></svg>
                    <p class="text-sm font-medium text-slate-700">
                        @if($search || $statusFilter) No classrooms match your filters. @else No classrooms created yet. @endif
                    </p>
                </div>
            @endif
        </section>
        
        {{-- Assign Faculty Modal (hidden) --}}
        <div id="assignModal" class="hidden fixed inset-0 z-50 grid place-items-center bg-black/40">
            <div class="bg-white rounded-2xl p-6 w-96">
                <h3 class="text-lg font-bold mb-3">Assign Faculty to <span id="modalClassName" class="font-semibold"></span></h3>
                <form id="assignForm" method="POST" action="">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm text-slate-700 mb-1">Faculty</label>
                        <select name="faculty_user_id" required class="w-full rounded border px-3 py-2 text-sm">
                            <option value="">Select faculty…</option>
                            @foreach(\App\Models\User::where('role', 'faculty')->orderBy('name')->get() as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" id="assignCancel" class="rounded border px-4 py-2 text-sm">Cancel</button>
                        <button type="submit" class="rounded bg-green-600 text-white px-4 py-2 text-sm">Assign</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');



                // Assign faculty modal
                const modal = document.getElementById('assignModal');
                const modalName = document.getElementById('modalClassName');
                const assignForm = document.getElementById('assignForm');
                document.querySelectorAll('.assign-faculty').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.getAttribute('data-id');
                        const name = btn.getAttribute('data-name');
                        modalName.textContent = name;
                        assignForm.action = '/admin/classrooms/' + id + '/assign-faculty';
                        modal.classList.remove('hidden');
                    });
                });

                document.getElementById('assignCancel').addEventListener('click', () => {
                    modal.classList.add('hidden');
                });

                // Export dropdown toggles per-row
                document.querySelectorAll('.export-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const menu = btn.parentElement.querySelector('.export-menu');
                        if (menu) { menu.classList.toggle('hidden'); }
                    });
                });

                // Classroom detail export toggle
                const exportToggle = document.getElementById('exportToggle');
                const exportMenu = document.getElementById('exportMenu');
                if (exportToggle && exportMenu) {
                    exportToggle.addEventListener('click', () => exportMenu.classList.toggle('hidden'));
                }
            })();
        </script>
    </div>
@endsection