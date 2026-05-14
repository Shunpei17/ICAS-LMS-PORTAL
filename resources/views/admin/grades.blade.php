@extends('layouts.admin')
@section('title', 'Grade Distribution')
@section('pageDescription', 'Monitor grade distributions and academic performance across all courses.')
@section('content')
<div class="space-y-6">
    {{-- Header + Export --}}
    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Grade Management</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-900">Grade Distribution</h2>
                <p class="mt-1 text-sm text-slate-500">Academic performance overview across all enrolled courses.</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.grades') }}" method="GET" class="flex flex-wrap items-center gap-2">
                    <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 outline-none transition focus:border-slate-300 focus:bg-white" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Pending" @selected(($statusFilter ?? '') === 'Pending')>Pending</option>
                        <option value="Verified" @selected(($statusFilter ?? '') === 'Verified')>Verified</option>
                    </select>
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer px-2">
                        <input type="checkbox" name="history" value="1" @checked(request()->has('history')) onchange="this.form.submit()" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        View History
                    </label>
                    <select name="academic_level" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 outline-none transition focus:border-slate-300 focus:bg-white" onchange="this.form.submit()">
                        <option value="">All Levels</option>
                        <option value="Senior High School" @selected($academicLevelFilter === 'Senior High School')>Senior High</option>
                        <option value="1st Year College" @selected($academicLevelFilter === '1st Year College')>1st Year</option>
                        <option value="2nd Year College" @selected($academicLevelFilter === '2nd Year College')>2nd Year</option>
                        <option value="3rd Year College" @selected($academicLevelFilter === '3rd Year College')>3rd Year</option>
                    </select>
                    <select name="course" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 outline-none transition focus:border-slate-300 focus:bg-white" onchange="this.form.submit()">
                        <option value="">All Courses</option>
                        <option value="BSIT" @selected($courseFilter === 'BSIT')>BSIT</option>
                        <option value="BSHM" @selected($courseFilter === 'BSHM')>BSHM</option>
                    </select>
                    <select name="strand" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 outline-none transition focus:border-slate-300 focus:bg-white" onchange="this.form.submit()">
                        <option value="">All Strands</option>
                        <option value="ICT" @selected(($strandFilter ?? '') === 'ICT')>ICT</option>
                        <option value="HE" @selected(($strandFilter ?? '') === 'HE')>HE</option>
                    </select>
                    <select name="subject" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 outline-none transition focus:border-slate-300 focus:bg-white" onchange="this.form.submit()">
                        <option value="">All Subjects</option>
                        @foreach($subjectOptions as $option)
                            <option value="{{ $option['code'] }}" @selected($subjectFilter === $option['code'])>
                                {{ $option['name'] }} ({{ $option['code'] }})
                            </option>
                        @endforeach
                    </select>
                </form>
                <form action="{{ route('admin.grades.export') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="academic_level" value="{{ $academicLevelFilter }}">
                    <input type="hidden" name="course" value="{{ $courseFilter }}">
                    <input type="hidden" name="strand" value="{{ $strandFilter ?? '' }}">
                    <input type="hidden" name="subject" value="{{ $subjectFilter }}">
                    <select name="format" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 outline-none transition focus:border-slate-300 focus:bg-white">
                        <option value="csv">Excel / CSV</option>
                        <option value="pdf">Official PDF Record</option>
                    </select>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Generate
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- Overall Stats --}}
    <div class="grid gap-4 sm:grid-cols-3">
        @foreach($overview as $o)
            @php $cc=match($o['color'] ?? 'slate'){
                'emerald'=>['bg-emerald-50','border-emerald-200','text-emerald-700'],'sky'=>['bg-sky-50','border-sky-200','text-sky-700'],default=>['bg-white','border-slate-200','text-slate-900']
            }; @endphp
            <div class="rounded-3xl {{ $cc[0] }} border {{ $cc[1] }} shadow-sm p-6">
                <p class="text-xs uppercase tracking-widest font-semibold text-slate-500">{{ $o['label'] }}</p>
                <p class="mt-3 text-4xl font-black {{ $cc[2] }}">{{ $o['value'] }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-6">Per-Subject Grade Distribution</h3>
        @php
        $gradeColors=['A'=>'bg-emerald-500','B'=>'bg-sky-500','C'=>'bg-amber-400','D'=>'bg-orange-400','F'=>'bg-rose-500'];
        @endphp

        <div class="space-y-6">
            @forelse($courses as $course)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                        <div class="flex items-start gap-4">
                            <div>
                                <p class="font-bold text-slate-900">{{ $course['name'] }}</p>
                                <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $course['code'] }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3 text-xs">
                            <div class="rounded-xl bg-white border border-slate-200 px-3 py-1.5 text-center">
                                <p class="font-black text-emerald-600 text-lg">{{ $course['avg'] }}%</p>
                                <p class="text-slate-400">Average</p>
                            </div>
                            <div class="rounded-xl bg-white border border-slate-200 px-3 py-1.5 text-center">
                                <p class="font-black text-sky-600 text-lg">{{ $course['passing'] }}%</p>
                                <p class="text-slate-400">Passing</p>
                            </div>
                            <div class="rounded-xl bg-white border border-slate-200 px-3 py-1.5 text-center">
                                <p class="font-black text-slate-900 text-lg">{{ $course['highest'] }}</p>
                                <p class="text-slate-400">Highest</p>
                            </div>
                            <div class="rounded-xl bg-white border border-slate-200 px-3 py-1.5 text-center">
                                <p class="font-black text-rose-500 text-lg">{{ $course['lowest'] }}</p>
                                <p class="text-slate-400">Lowest</p>
                            </div>
                        </div>
                    </div>

                    {{-- Grade Distribution Bar --}}
                    @php $total = array_sum($course['dist']); @endphp
                    <div class="space-y-2">
                        @foreach($course['dist'] as $letter => $count)
                            @php $pct = $total > 0 ? round(($count/$total)*100) : 0; @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-5 text-xs font-black text-slate-600 text-center">{{ $letter }}</span>
                                <div class="flex-1 h-5 rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full rounded-full {{ $gradeColors[$letter] }} transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="flex items-center gap-1.5 w-20 text-xs text-right">
                                    <span class="font-bold text-slate-900">{{ $count }}</span>
                                    <span class="text-slate-400">({{ $pct }}%)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-slate-500">No graded courses found.</div>
            @endforelse
        </div>
    </section>

    {{-- Consolidated Grades Table (All/Pending/Verified) --}}
</div>

    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6 mt-6">
        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
            Student Grade Management
            <span class="bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded-full">{{ $allGrades->total() }} total</span>
        </h3>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Student Name</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Course / Strand</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Level</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Subject</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Grade</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-slate-500 text-xs uppercase tracking-wide text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($allGrades as $grade)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $grade->user->name ?? 'Unknown' }}</td>
                        <td class="px-5 py-3.5 text-slate-600 font-medium">
                            @if(str_contains($grade->user->academic_level ?? '', 'Senior High School'))
                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[10px] uppercase font-bold mr-1">Strand</span>
                                {{ $grade->user->strand ?? 'N/A' }}
                            @else
                                <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-[10px] uppercase font-bold mr-1">Course</span>
                                {{ $grade->user->course ?? 'N/A' }}
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-400">{{ $grade->user->academic_level ?? 'N/A' }}</td>
                        <td class="px-5 py-3.5 text-slate-700">{{ $grade->module_name }} <span class="text-xs text-slate-400">({{ $grade->module_code }})</span></td>
                        <td class="px-5 py-3.5"><span class="font-bold text-slate-900">{{ $grade->grade_percent }}%</span></td>
                        <td class="px-5 py-3.5">
                            @if($grade->grade_verified)
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] uppercase font-bold px-1.5 py-0.5 rounded">Verified</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 text-[10px] uppercase font-bold px-1.5 py-0.5 rounded">Pending</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="inline-flex items-center gap-2">
                                @if(!$grade->grade_verified)
                                <form action="{{ route('admin.grades.verify', $grade->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-xl hover:bg-emerald-200 transition">Verify</button>
                                </form>
                                @endif
                                <button type="button" onclick="openEditModal({{ $grade->id }}, {{ $grade->grade_percent }})" class="text-xs font-semibold bg-slate-100 text-slate-700 px-3 py-1.5 rounded-xl hover:bg-slate-200 transition">Edit</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-6 text-center text-slate-500">No grades found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $allGrades->links() }}
        </div>
    </section>
</div>

<!-- Edit Grade Modal -->
<div id="editGradeModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Edit Student Grade (Admin Override)</h3>
        <form id="editGradeForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">New Grade (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="grade_percent" id="modalGradeInput" required class="w-full rounded-xl border border-slate-200 px-4 py-2 text-slate-900 focus:border-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reason for Change</label>
                    <textarea name="reason" required rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-slate-900 focus:border-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/20" placeholder="e.g., Authorized manual correction, recalculated..."></textarea>
                    <p class="mt-1 text-xs text-slate-500">This change will be logged in the Audit Trail.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(recordId, currentGrade) {
        document.getElementById('editGradeForm').action = `/admin/grades/${recordId}/update`;
        document.getElementById('modalGradeInput').value = currentGrade;
        document.getElementById('editGradeModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editGradeModal').classList.add('hidden');
    }
</script>
@endsection