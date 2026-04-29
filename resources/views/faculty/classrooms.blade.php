@extends('layouts.faculty')

@section('title', 'Classrooms')
@section('pageDescription', 'Manage your classrooms, students, and academic records.')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="rounded-3xl bg-gradient-to-r from-green-500 to-emerald-600 p-6 shadow-md text-white flex-1">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold">My Classrooms</h2>
                        <p class="mt-1 text-green-100 text-sm">{{ count($classrooms) }} classroom{{ count($classrooms) !== 1 ? 's' : '' }} — Create and manage your teaching spaces.</p>
                    </div>
                    <a href="{{ route('faculty.classrooms.create') }}"
                       class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-green-700 hover:bg-green-50 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Classroom
                    </a>
                </div>
            </div>
        </div>

        {{-- Classroom Cards --}}
        @if(count($classrooms) > 0)
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach($classrooms as $room)
                    @php
                        $statusColor = $room['status'] === 'active'
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-slate-100 text-slate-500';
                    @endphp
                    <article class="rounded-3xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-green-300 transition-all flex flex-col">
                        <div class="p-6 flex-1">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="h-12 w-12 rounded-2xl bg-green-100 text-green-700 grid place-items-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1z"></path></svg>
                                </div>
                                <span class="inline-flex rounded-full {{ $statusColor }} px-3 py-1 text-xs font-bold capitalize flex-shrink-0">{{ ucfirst($room['status']) }}</span>
                            </div>

                            <h3 class="text-lg font-bold text-slate-900">{{ $room['name'] }}</h3>
                            <p class="text-sm font-mono text-slate-500 mt-0.5">{{ $room['code'] }}</p>

                            @if($room['description'])
                                <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ $room['description'] }}</p>
                            @endif

                            <div class="mt-4 space-y-1.5 text-xs text-slate-600">
                                @if($room['schedule'])
                                    <p class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $room['schedule'] }}
                                    </p>
                                @endif
                                <p class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"></path></svg>
                                    <span><strong>{{ $room['student_count'] }}</strong> student{{ $room['student_count'] !== 1 ? 's' : '' }} enrolled</span>
                                </p>
                            </div>

                            {{-- Stats Row --}}
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3 text-center">
                                    <p class="text-lg font-black text-emerald-600">{{ $room['avg_grade'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Avg Grade</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3 text-center">
                                    <p class="text-lg font-black text-sky-600">{{ $room['attendance_rate'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Attendance</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 p-4 flex gap-2">
                            <a href="{{ route('faculty.classrooms.show', $room['id']) }}"
                               class="flex-1 rounded-2xl bg-green-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-green-700 transition">
                                Open
                            </a>
                            <a href="{{ route('faculty.classrooms.edit', $room['id']) }}"
                               class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                                Edit
                            </a>
                            <div class="relative">
                                <button data-export-toggle="{{ $room['id'] }}" type="button" class="ml-2 inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                                    Export ▾
                                </button>
                                <div id="export-menu-{{ $room['id'] }}" class="hidden absolute right-0 mt-2 w-40 rounded-xl bg-white border border-slate-100 shadow-lg z-50">
                                    <a href="{{ route('faculty.classrooms.export', $room['id']) }}?format=csv" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download CSV</a>
                                    <a href="{{ route('faculty.classrooms.export', $room['id']) }}?format=xlsx" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download Excel</a>
                                    <a href="{{ route('faculty.classrooms.export', $room['id']) }}?format=pdf" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download PDF</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <script>
                document.addEventListener('click', function(e){
                    // close any open export menus when clicking outside
                    if(!e.target.matches('[data-export-toggle]') && !e.target.closest('[id^="export-menu-"]')){
                        document.querySelectorAll('[id^="export-menu-"]').forEach(function(el){ el.classList.add('hidden'); });
                    }
                });

                document.querySelectorAll('[data-export-toggle]').forEach(function(btn){
                    btn.addEventListener('click', function(e){
                        var id = this.getAttribute('data-export-toggle');
                        var menu = document.getElementById('export-menu-' + id);
                        if(menu) menu.classList.toggle('hidden');
                    });
                });
            </script>
        @else
            <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-16 text-center">
                <div class="mx-auto h-16 w-16 rounded-3xl bg-green-50 text-green-500 grid place-items-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v1H9V7zm5 0h1v1h-1V7zm-5 4h1v1H9v-1zm5 0h1v1h-1v-1z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">No classrooms yet</h3>
                <p class="mt-2 text-slate-500 text-sm">Create your first classroom to start managing students, attendance and grades.</p>
                <a href="{{ route('faculty.classrooms.create') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-green-600 px-6 py-3 text-sm font-bold text-white hover:bg-green-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Create First Classroom
                </a>
            </section>
        @endif
    </div>
@endsection
