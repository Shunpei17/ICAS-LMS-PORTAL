@extends('layouts.faculty')

@section('title', $classroom->name)
@section('pageDescription', 'Classroom detail — students, attendance, and grades.')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb & Header --}}
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('faculty.classrooms') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Classrooms
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-sm font-semibold text-slate-700">{{ $classroom->name }}</span>
        </div>

        {{-- Classroom Info Banner --}}
        <section class="rounded-3xl bg-gradient-to-r from-green-500 to-emerald-600 p-6 text-white shadow-md">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="rounded-xl bg-white/20 px-3 py-1 text-sm font-bold font-mono">{{ $classroom->code }}</span>
                        <span class="rounded-full {{ $classroom->status === 'active' ? 'bg-emerald-300 text-emerald-900' : 'bg-slate-300 text-slate-700' }} px-3 py-1 text-xs font-bold capitalize">
                            {{ ucfirst($classroom->status) }}
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold">{{ $classroom->name }}</h2>
                    @if($classroom->schedule)
                        <p class="mt-1 text-green-100 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $classroom->schedule }}
                        </p>
                    @endif
                    @if($classroom->description)
                        <p class="mt-2 text-green-100 text-sm max-w-lg">{{ $classroom->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('faculty.classrooms.edit', $classroom->id) }}"
                       class="rounded-2xl bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-semibold text-white transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </a>
                    <div class="relative">
                        <button id="classroom-export-toggle" type="button" class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">Export ▾</button>
                        <div id="classroom-export-menu" class="hidden absolute right-0 mt-2 w-40 rounded-xl bg-white border border-slate-100 shadow-lg z-50">
                            <a href="{{ route('faculty.classrooms.export', $classroom->id) }}?format=csv" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download CSV</a>
                            <a href="{{ route('faculty.classrooms.export', $classroom->id) }}?format=xlsx" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download Excel</a>
                            <a href="{{ route('faculty.classrooms.export', $classroom->id) }}?format=pdf" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Download PDF</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6 text-center">
                <p class="text-4xl font-black text-green-600">{{ count($students) }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-500">Students</p>
            </div>
            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6 text-center">
                <p class="text-4xl font-black text-sky-600">{{ $avgGrade ?? '—' }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-500">Average Grade</p>
            </div>
            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6 text-center">
                <p class="text-4xl font-black text-violet-600">{{ count($attendanceRecords) }}</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-500">Attendance Records</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <div class="space-y-6">
                {{-- Content Management (Decentralized Authority) --}}
                <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Topics & Content</h3>
                            <p class="text-sm text-slate-500">Manage your classroom topics, materials, and assignments.</p>
                        </div>
                        <button onclick="document.getElementById('addTopicModal').classList.remove('hidden')" 
                                class="rounded-xl bg-green-600 px-4 py-2 text-xs font-bold text-white hover:bg-green-700 transition">
                            + New Topic
                        </button>
                    </div>

                    @forelse($classroom->topics as $topic)
                        <div class="mb-6 last:mb-0">
                            <div class="flex items-center justify-between bg-slate-50 rounded-2xl px-5 py-3 border border-slate-100 mb-3">
                                <h4 class="font-bold text-slate-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    {{ $topic->name }}
                                </h4>
                                <div class="flex items-center gap-2">
                                    <button onclick="openAddMaterialModal({{ $topic->id }}, '{{ $topic->name }}')" 
                                            class="text-[10px] font-bold uppercase tracking-wider text-green-600 hover:text-green-700">
                                        + Add Content
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <form action="{{ route('faculty.classrooms.topics.destroy', [$classroom->id, $topic->id]) }}" method="POST" onsubmit="return confirm('Delete this topic and all its contents?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[10px] font-bold uppercase tracking-wider text-rose-500 hover:text-rose-600">Delete</button>
                                    </form>
                                </div>
                            </div>

                            <div class="grid gap-3 ml-4">
                                @forelse($topic->materials as $mat)
                                    @php
                                        $icon = match($mat->type) {
                                            'assignment' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>',
                                            'quiz' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                                            default => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>',
                                        };
                                        $typeColor = match($mat->type) {
                                            'assignment' => 'bg-amber-100 text-amber-700',
                                            'quiz' => 'bg-rose-100 text-rose-700',
                                            default => 'bg-blue-100 text-blue-700',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-white shadow-sm hover:shadow-md transition group">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-lg {{ $typeColor }} grid place-items-center">
                                                {!! $icon !!}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-800">{{ $mat->title }}</p>
                                                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-tight">{{ $mat->type }}</p>
                                            </div>
                                        </div>
                                        <form action="{{ route('faculty.classrooms.materials.destroy', [$classroom->id, $mat->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="opacity-0 group-hover:opacity-100 p-1.5 text-slate-400 hover:text-rose-500 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic ml-2">No content items in this topic.</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 border border-dashed border-slate-200 rounded-3xl bg-slate-50">
                            <p class="text-sm text-slate-500">Your classroom content is empty.</p>
                            <p class="text-xs text-slate-400 mt-1">Start by creating your first topic above.</p>
                        </div>
                    @endforelse
                </section>

                {{-- Enrolled Students --}}
                <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-1">Enrolled Students</h3>
                    <p class="text-sm text-slate-500 mb-5">{{ count($students) }} student{{ count($students) !== 1 ? 's' : '' }} in this classroom.</p>

                    @if(count($students) > 0)
                        <div class="space-y-3">
                            @foreach($students as $student)
                                @php
                                    $statusBadge = match($student['enrollment_status']) {
                                        'enrolled' => 'bg-emerald-100 text-emerald-700',
                                        'dropped'  => 'bg-rose-100 text-rose-700',
                                        default    => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <article class="flex items-center gap-4 rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                    <div class="h-10 w-10 flex-shrink-0 rounded-full bg-green-600 grid place-items-center text-white text-sm font-bold">
                                        {{ $student['initials'] }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-900 truncate">{{ $student['name'] }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $student['email'] }}</p>
                                        <div class="flex flex-wrap gap-2 mt-1.5">
                                            <span class="inline-flex rounded-full {{ $statusBadge }} px-2 py-0.5 text-xs font-semibold capitalize">
                                                {{ ucfirst($student['enrollment_status']) }}
                                            </span>
                                            @if($student['section'])
                                                <span class="inline-flex rounded-full bg-sky-100 text-sky-700 px-2 py-0.5 text-xs font-semibold">Sec: {{ $student['section'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-lg font-black {{ $student['grade'] ? 'text-slate-900' : 'text-slate-300' }}">
                                            {{ $student['grade'] ?? '—' }}
                                        </p>
                                        @if($student['attendance_rate'])
                                            <p class="text-xs text-slate-400">{{ $student['attendance_rate'] }} present</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                            <p class="text-sm text-slate-500">No students have joined this classroom yet.</p>
                            <p class="text-xs text-slate-400 mt-1">Students enroll via the Student Portal → Classrooms.</p>
                        </div>
                    @endif
                </section>
            </div>

            {{-- Attendance + Grades Side Panel --}}
            <div class="space-y-5">
                {{-- Grade Summary --}}
                <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Grade Summary</h3>
                    @if(count($gradeRecords) > 0)
                        <div class="space-y-2">
                            @foreach($gradeRecords as $g)
                                @php $pct = min(100, (int) $g['value']); @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-medium text-slate-700 truncate max-w-[60%]">{{ $g['name'] }}</span>
                                        <span class="font-bold text-slate-900">{{ $g['grade'] }}</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $pct >= 85 ? 'bg-emerald-500' : ($pct >= 75 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400">No grade data yet for this classroom.</p>
                    @endif
                </section>

                {{-- Attendance Log --}}
                <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Recent Attendance</h3>
                    @if(count($attendanceRecords) > 0)
                        <div class="space-y-2 max-h-72 overflow-y-auto">
                            @foreach(array_slice($attendanceRecords, 0, 20) as $record)
                                @php
                                    $aBadge = match($record['status']) {
                                        'Present' => 'bg-emerald-100 text-emerald-700',
                                        'Late'    => 'bg-amber-100 text-amber-700',
                                        default   => 'bg-rose-100 text-rose-700',
                                    };
                                @endphp
                                <div class="flex items-center justify-between gap-3 text-xs rounded-xl bg-slate-50 px-3 py-2">
                                    <span class="font-medium text-slate-700 truncate">{{ $record['student_name'] }}</span>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-slate-400">{{ $record['date'] }}</span>
                                        <span class="inline-flex rounded-full {{ $aBadge }} px-2 py-0.5 font-semibold">{{ $record['status'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if(count($attendanceRecords) > 20)
                            <p class="mt-2 text-xs text-slate-400 text-center">Showing 20 of {{ count($attendanceRecords) }} records. View full report in Grade Management.</p>
                        @endif
                    @else
                        <p class="text-sm text-slate-400">No attendance records logged for this classroom yet.</p>
                        <a href="{{ route('faculty.grades') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-green-600 hover:underline">
                            Go to Grade Management →
                        </a>
                    @endif
                </section>
            </div>
        </div>
    </div>
    {{-- Modals --}}
    <div id="addTopicModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Add New Topic</h3>
            <form action="{{ route('faculty.classrooms.topics.store', $classroom->id) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Topic Name</label>
                        <input type="text" name="name" required placeholder="e.g., Unit 1: Introduction" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 focus:border-green-500 focus:outline-none">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addTopicModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-slate-500">Cancel</button>
                    <button type="submit" class="rounded-xl bg-green-600 px-5 py-2 text-sm font-bold text-white hover:bg-green-700 transition">Save Topic</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addMaterialModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Add Content to <span id="targetTopicName" class="text-green-600"></span></h3>
            <p class="text-xs text-slate-400 mb-4 uppercase tracking-widest">Materials & Assignments</p>
            <form action="{{ route('faculty.classrooms.materials.store', $classroom->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="topic_id" id="targetTopicId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Title</label>
                        <input type="text" name="title" required placeholder="e.g., Week 1 Reading List" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 focus:border-green-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Type</label>
                        <select name="type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 focus:border-green-500 focus:outline-none">
                            <option value="material">Study Material</option>
                            <option value="assignment">Assignment</option>
                            <option value="quiz">Quiz / Exam</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Description / Body</label>
                        <textarea name="body" rows="3" placeholder="Optional instructions..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 focus:border-green-500 focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Attachment (Optional)</label>
                        <input type="file" name="file" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('addMaterialModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-slate-500">Cancel</button>
                    <button type="submit" class="rounded-xl bg-green-600 px-5 py-2 text-sm font-bold text-white hover:bg-green-700 transition">Upload Content</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function(){
            var toggle = document.getElementById('classroom-export-toggle');
            var menu = document.getElementById('classroom-export-menu');
            document.addEventListener('click', function(e){
                if(toggle && toggle.contains(e.target)){
                    menu.classList.toggle('hidden');
                    return;
                }
                if(menu && !menu.contains(e.target)){
                    menu.classList.add('hidden');
                }
            });

            window.openAddMaterialModal = function(topicId, topicName) {
                document.getElementById('targetTopicId').value = topicId;
                document.getElementById('targetTopicName').textContent = topicName;
                document.getElementById('addMaterialModal').classList.remove('hidden');
            };
        })();
    </script>
@endsection
