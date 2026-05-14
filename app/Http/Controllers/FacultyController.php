<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacultyAttendanceRecordRequest;
use App\Http\Requests\UpdateFacultyAttendanceRecordRequest;
use App\Models\Classroom;
use App\Models\FacultyAttendanceRecord;
use App\Models\Grade;
use App\Models\StudentModuleRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacultyController extends Controller
{
    public function profile(): View
    {
        $user = Auth::user();

        return view('faculty.profile', [
            'faculty' => [
                'faculty_id' => 'FAC-'.str_pad($user->id ?? 1, 4, '0', STR_PAD_LEFT),
                'name' => $user->name ?? 'Dr. Maria Santos',
                'email' => $user->email ?? 'faculty@icas.edu',
                'phone' => '+63 917 654 3210',
                'department' => 'College of Engineering & Technology',
                'designation' => 'Associate Professor',
                'office' => 'Faculty Office, 3rd Floor CET Building',
                'office_hours' => 'Mon, Wed, Fri — 10:00 AM to 12:00 PM',
                'subjects' => ['Advanced Mathematics (MATH301)', 'Physics I (PHY201)', 'World History (HIST201)', 'English Composition (ENG101)'],
                'status' => 'Active',
            ],
        ]);
    }

    public function schedule(): View
    {
        $schedule = [
            'Mon' => [
                ['time' => '7:00 AM – 8:30 AM',  'subject' => 'Advanced Mathematics', 'code' => 'MATH301', 'room' => 'Room 201', 'students' => 28],
                ['time' => '9:00 AM – 10:30 AM', 'subject' => 'English Composition',  'code' => 'ENG101',  'room' => 'Room 105', 'students' => 22],
            ],
            'Tue' => [
                ['time' => '10:00 AM – 11:30 AM', 'subject' => 'Physics I',    'code' => 'PHY201',  'room' => 'Lab 3',    'students' => 24],
                ['time' => '1:00 PM – 2:30 PM',   'subject' => 'World History', 'code' => 'HIST201', 'room' => 'Room 310', 'students' => 30],
            ],
            'Wed' => [
                ['time' => '7:00 AM – 8:30 AM',  'subject' => 'Advanced Mathematics', 'code' => 'MATH301', 'room' => 'Room 201', 'students' => 28],
                ['time' => '9:00 AM – 10:30 AM', 'subject' => 'English Composition',  'code' => 'ENG101',  'room' => 'Room 105', 'students' => 22],
            ],
            'Thu' => [
                ['time' => '10:00 AM – 11:30 AM', 'subject' => 'Physics I',    'code' => 'PHY201',  'room' => 'Lab 3',    'students' => 24],
                ['time' => '1:00 PM – 2:30 PM',   'subject' => 'World History', 'code' => 'HIST201', 'room' => 'Room 310', 'students' => 30],
            ],
            'Fri' => [
                ['time' => '7:00 AM – 8:30 AM', 'subject' => 'Advanced Mathematics', 'code' => 'MATH301', 'room' => 'Room 201', 'students' => 28],
            ],
            'Sat' => [],
        ];
        $totalStudents = 28 + 22 + 24 + 30;

        return view('faculty.schedule', compact('schedule', 'totalStudents'));
    }

    public function dashboard(): View
    {
        $stats = [
            ['label' => 'My Courses', 'value' => '1', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>'],
            ['label' => 'Total Students', 'value' => '28', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>'],
            ['label' => 'Graded', 'value' => '2', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>'],
            ['label' => 'Avg Performance', 'value' => '87%', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>'],
        ];

        $courses = [
            ['name' => 'Advanced Mathematics', 'code' => 'MATH301', 'schedule' => 'Mon, Wed, Fri 9:00 AM', 'students' => 28, 'grade' => '10th'],
        ];

        return view('faculty.dashboard', compact('stats', 'courses'));
    }

    public function students()
    {
        return redirect()->route('faculty.classrooms');
    }

    public function subjectShow(string $slug)
    {
        return redirect()->route('faculty.classrooms');
    }

    public function studentShow(string $id): View
    {
        // Placeholder student details for faculty dashboard
        $student = [
            'id' => $id,
            'student_id' => 'STU-'.str_pad($id, 4, '0', STR_PAD_LEFT),
            'name' => 'Miguel Santos',
            'email' => 'miguel.s@school.edu',
            'phone' => '+63 912 345 6789',
            'program' => 'BS Information Technology',
            'year_level' => '3rd Year',
            'overall_attendance' => '95%',
            'overall_grade' => '90%',
            'performance_trend' => 'Improving',
        ];

        $subjectGrades = [
            ['code' => 'MATH301', 'name' => 'Advanced Mathematics', 'grade' => '92%', 'attendance' => 'Present (12/12)'],
            ['code' => 'PHY201', 'name' => 'Physics I', 'grade' => '88%', 'attendance' => 'Present (10/12)'],
            ['code' => 'ENG101', 'name' => 'English Composition', 'grade' => '95%', 'attendance' => 'Present (12/12)'],
        ];

        $recentActivity = [
            ['action' => 'Submitted Assignment', 'subject' => 'MATH301 - Problem Set 3', 'date' => '2 days ago', 'icon' => 'assign', 'color' => 'amber'],
            ['action' => 'Completed Quiz', 'subject' => 'PHY201 - Quiz 1', 'date' => '5 days ago', 'icon' => 'quiz', 'color' => 'rose'],
            ['action' => 'Viewed Material', 'subject' => 'ENG101 - Syllabus', 'date' => '1 week ago', 'icon' => 'doc', 'color' => 'slate'],
        ];

        return view('faculty.student-show', compact('student', 'subjectGrades', 'recentActivity'));
    }

    public function grades(Request $request): View
    {
        $tab = $request->query('tab') === 'grades' ? 'grades' : 'attendance';
        $filters = $this->resolveGradesFilters($request);
        $activeFilters = collect($filters)
            ->filter(function (string $value): bool {
                return $value !== '';
            })
            ->all();

        $baseQuery = $this->queryAttendanceRecords($filters);

        $totalRecords = (clone $baseQuery)->count();
        $presentRecords = (clone $baseQuery)->where('status', 'Present')->count();
        $absentRecords = (clone $baseQuery)->where('status', 'Absent')->count();
        $lateRecords = (clone $baseQuery)->where('status', 'Late')->count();

        $attendanceRate = $totalRecords > 0
            ? (string) round(($presentRecords / $totalRecords) * 100).'%'
            : '0%';

        $summary = [
            ['label' => 'Attendance Rate', 'value' => $attendanceRate],
            ['label' => 'Present', 'value' => (string) $presentRecords],
            ['label' => 'Absent', 'value' => (string) $absentRecords],
            ['label' => 'Late', 'value' => (string) $lateRecords],
        ];

        $records = (clone $baseQuery)
            ->orderByDesc('attendance_date')
            ->orderBy('student_name')
            ->get()
            ->map(function (FacultyAttendanceRecord $record): array {
                return [
                    'id' => $record->id,
                    'initials' => $this->extractInitials($record->student_name),
                    'name' => $record->student_name,
                    'class' => $record->student_class,
                    'date' => $record->attendance_date->format('n/j/Y'),
                    'status' => $record->status,
                ];
            })
            ->all();

        $classOptions = FacultyAttendanceRecord::query()
            ->where('faculty_user_id', Auth::id())
            ->select('student_class')
            ->distinct()
            ->orderBy('student_class')
            ->pluck('student_class')
            ->all();

        $gradeSubjects = $facultyClassrooms->pluck('code')->all();

        // If no subject selected, default to the first classroom if available
        if (!$gradeSubjectFilter && !empty($gradeSubjects)) {
            $gradeSubjectFilter = $gradeSubjects[0];
        }

        // Get criteria for selected subject
        $activeClassroom = $facultyClassrooms->where('code', $gradeSubjectFilter)->first();
        $activeCriteria = $activeClassroom ? $activeClassroom->gradingCriteria : collect();

        $studentsWithGrades = collect();
        if ($tab === 'grades') {
            // Get students enrolled in this classroom or all students for now (simplification)
            $studentsQuery = User::where('role', 'student');
            if ($gradeSearch) {
                $studentsQuery->where('name', 'like', '%'.$gradeSearch.'%');
            }
            $students = $studentsQuery->get();

            $gradeQuery = Grade::query();
            if ($gradeSubjectFilter) {
                $gradeQuery->where('subject_id', $gradeSubjectFilter);
            }
            $existingGrades = $gradeQuery->get()->keyBy('student_id');

            foreach ($students as $student) {
                $existingGrade = $existingGrades->get($student->id);
                $componentScores = $existingGrade?->component_scores ?? [];
                
                $studentsWithGrades->push([
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'subject_id' => $gradeSubjectFilter,
                    'component_scores' => $componentScores,
                    'quiz' => $existingGrade?->quiz,
                    'assignment' => $existingGrade?->assignment,
                    'exam' => $existingGrade?->exam,
                    'average' => $existingGrade?->average,
                    'remarks' => $existingGrade?->remarks,
                ]);
            }
        }

        // Load classrooms with their grading criteria for the criteria config section
        /** @var User $faculty */
        $faculty = Auth::user();
        $facultyClassrooms = $faculty->classroomsAsFaculty()
            ->with('gradingCriteria')
            ->orderBy('name')
            ->get();

        return view('faculty.grades', compact('summary', 'records', 'filters', 'activeFilters', 'classOptions', 'tab', 'studentsWithGrades', 'gradeSubjects', 'gradeSubjectFilter', 'gradeSearch', 'facultyClassrooms', 'activeCriteria', 'activeClassroom'));
    }

    public function storeAttendanceRecord(StoreFacultyAttendanceRecordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $classroom = null;
        if (! empty($data['student_class'])) {
            $classroom = Classroom::where('code', $data['student_class'])->first();
        }

        if ($classroom !== null) {
            $this->authorize('manage', $classroom);
        }

        // Prevent duplicate attendance records for same student/date/faculty/subject/session
        $existingQuery = FacultyAttendanceRecord::query()
            ->whereDate('attendance_date', $data['attendance_date'])
            ->where('faculty_user_id', Auth::id())
            ->where('subject_code', $data['subject_code'] ?? '')
            ->where('student_class', $data['student_class'] ?? '');

        if (! empty($data['student_user_id'])) {
            $existingQuery->where('student_user_id', $data['student_user_id']);
        } else {
            $existingQuery->where('student_name', $data['student_name']);
        }

        $existing = $existingQuery->first();

        if ($existing) {
            // If request asks to update existing record, perform update
            if ($request->boolean('update_if_exists')) {
                $existing->update(['status' => $data['status']]);

                return redirect()
                    ->route('faculty.grades')
                    ->with('status', 'Existing attendance updated successfully.');
            }

            // Otherwise, block creation and notify user
            return redirect()
                ->route('faculty.grades')
                ->withErrors(['attendance' => 'Attendance already recorded for this student today.']);
        }

        try {
            // capture student snapshot data (course, academic_level) if mapped
            $studentCourse = null;
            $studentLevel = null;
            
            $student = null;
            if (! empty($data['student_user_id'])) {
                $student = User::find($data['student_user_id']);
            } else {
                // Try finding by name (inexact but helpful for the current UI)
                $student = User::where('name', $data['student_name'])->where('role', 'student')->first();
            }

            if ($student !== null) {
                $studentCourse = $student->course;
                $studentLevel = $student->academic_level;
            }

            $settings = new \App\Services\SystemSettingsService();
            FacultyAttendanceRecord::query()->create(array_merge([
                'faculty_user_id' => Auth::id(),
                'course_strand' => $studentCourse,
                'academic_level' => $studentLevel,
                'academic_year' => $settings->get('academic_year'),
                'semester' => $settings->get('current_semester'),
                'student_user_id' => $student?->id,
            ], $data));

            return redirect()
                ->route('faculty.grades')
                ->with('status', 'Attendance record registered successfully.');
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            // SQLSTATE 23000 is integrity constraint violation (unique index), handle gracefully
            if ($sqlState === '23000') {
                return redirect()
                    ->route('faculty.grades')
                    ->withErrors(['attendance' => 'Attendance already recorded for this student today.']);
            }

            throw $e;
        }
    }

    public function exportAttendanceRecords(Request $request): StreamedResponse
    {
        $filters = $this->resolveGradesFilters($request);

        $records = $this->queryAttendanceRecords($filters)
            ->orderByDesc('attendance_date')
            ->orderBy('student_name')
            ->get();

        $filename = 'attendance-records-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($records): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fputcsv($output, ['Student Name', 'Class/Subject', 'Course/Strand', 'Academic Level', 'Date', 'Status']);

            foreach ($records as $record) {
                fputcsv($output, [
                    $record->student_name,
                    $record->student_class,
                    $record->course_strand,
                    $record->academic_level,
                    $record->attendance_date?->format('Y-m-d') ?? '',
                    $record->status,
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function updateAttendanceRecord(
        UpdateFacultyAttendanceRecordRequest $request,
        FacultyAttendanceRecord $attendanceRecord
    ): RedirectResponse {
        if ($attendanceRecord->faculty_user_id !== Auth::id()) {
            abort(403);
        }

        $attendanceRecord->update($request->validated());

        $routeParameters = collect($request->only([
            'search',
            'status',
            'student_class',
            'date',
        ]))
            ->filter(function (?string $value): bool {
                return $value !== null && $value !== '';
            })
            ->all();

        return redirect()
            ->route('faculty.grades', $routeParameters)
            ->with('status', 'Attendance record updated successfully.');
    }

    /**
     * Load existing attendance records for a specific date and class (for pre-fill logic)
     */
    public function loadTodayAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'student_class' => 'required|string',
            'attendance_date' => 'required|date',
        ]);

        $records = FacultyAttendanceRecord::query()
            ->where('faculty_user_id', Auth::id())
            ->where('student_class', $request->input('student_class'))
            ->whereDate('attendance_date', $request->input('attendance_date'))
            ->get(['id', 'student_name', 'status', 'attendance_date'])
            ->map(function (FacultyAttendanceRecord $record) {
                return [
                    'id' => $record->id,
                    'student_name' => $record->student_name,
                    'status' => $record->status,
                    'date' => $record->attendance_date->format('Y-m-d'),
                ];
            })
            ->all();

        return response()->json([
            'exists' => ! empty($records),
            'count' => count($records),
            'records' => $records,
            'message' => empty($records)
                ? 'No attendance records for this class today.'
                : count($records).' record(s) already submitted for today.',
        ]);
    }

    /**
     * @return array{search: string, status: string, student_class: string, date: string}
     */
    private function resolveGradesFilters(Request $request): array
    {
        $status = trim((string) $request->query('status', ''));

        if (! in_array($status, ['Present', 'Absent', 'Late'], true)) {
            $status = '';
        }

        return [
            'search' => trim((string) $request->query('search', '')),
            'status' => $status,
            'student_class' => trim((string) $request->query('student_class', '')),
            'date' => trim((string) $request->query('date', '')),
        ];
    }

    /**
     * @param  array{search: string, status: string, student_class: string, date: string}  $filters
     */
    private function queryAttendanceRecords(array $filters): Builder
    {
        $settings = new \App\Services\SystemSettingsService();
        return FacultyAttendanceRecord::query()
            ->where('faculty_user_id', Auth::id())
            ->where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->where('student_name', 'like', '%'.$filters['search'].'%');
            })
            ->when($filters['status'] !== '', function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when($filters['student_class'] !== '', function (Builder $query) use ($filters): void {
                $query->where('student_class', $filters['student_class']);
            })
            ->when($filters['date'] !== '', function (Builder $query) use ($filters): void {
                $query->whereDate('attendance_date', $filters['date']);
            });
    }

    public function enrollments(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['pending', 'enrolled', 'dropped'], true)
            ? $request->query('tab')
            : 'pending';

        $courseFilter = trim((string) $request->query('course', ''));

        $settings = new \App\Services\SystemSettingsService();
        $enrollments = StudentModuleRecord::query()
            ->where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
            ->when($tab === 'enrolled', fn ($q) => $q->whereIn('enrollment_status', ['faculty_approved', 'enrolled']))
            ->when($tab !== 'enrolled', fn ($q) => $q->where('enrollment_status', $tab))
            ->with(['user:id,name,email'])
            ->when($courseFilter !== '', function ($query) use ($courseFilter): void {
                $query->where('module_code', $courseFilter);
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $enrolledCount = StudentModuleRecord::where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
            ->whereIn('enrollment_status', ['faculty_approved', 'enrolled'])->count();
        $pendingCount = StudentModuleRecord::where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
            ->where('enrollment_status', 'pending')->count();
        $droppedCount = StudentModuleRecord::where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
            ->where('enrollment_status', 'dropped')->count();

        $summary = [
            ['label' => 'Pending',  'value' => (string) $pendingCount,  'color' => 'amber',   'tab' => 'pending'],
            ['label' => 'Enrolled', 'value' => (string) $enrolledCount, 'color' => 'emerald', 'tab' => 'enrolled'],
            ['label' => 'Dropped',  'value' => (string) $droppedCount,  'color' => 'rose',    'tab' => 'dropped'],
        ];

        $courseOptions = StudentModuleRecord::query()
            ->select('module_code', 'module_name')
            ->distinct()
            ->orderBy('module_name')
            ->get()
            ->map(fn (StudentModuleRecord $r): array => [
                'code' => $r->module_code,
                'name' => $r->module_name,
            ])
            ->all();

        return view('faculty.enrollments', compact('enrollments', 'summary', 'tab', 'courseFilter', 'courseOptions'));
    }

    public function approveEnrollment(StudentModuleRecord $moduleRecord): RedirectResponse
    {
        $moduleRecord->update(['enrollment_status' => 'faculty_approved']);

        return redirect()
            ->route('faculty.enrollments', ['tab' => 'pending'])
            ->with('status', 'Enrollment approved for '.$moduleRecord->user->name.' in '.$moduleRecord->module_name.'. Waiting for Admin verification.');
    }

    public function assignSection(Request $request, StudentModuleRecord $moduleRecord): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string', 'max:50'],
        ]);

        $moduleRecord->update(['section' => $validated['section']]);

        return redirect()
            ->route('faculty.enrollments', ['tab' => $request->query('tab', 'pending')])
            ->with('status', 'Section assigned: '.$validated['section'].' for '.$moduleRecord->user->name.'.');
    }

    private function extractInitials(string $name): string
    {
        $segments = preg_split('/\s+/', trim($name)) ?: [];

        $initials = collect($segments)
            ->filter()
            ->take(2)
            ->map(function (string $segment): string {
                return strtoupper(substr($segment, 0, 1));
            })
            ->implode('');

        return $initials !== '' ? $initials : 'NA';
    }

    public function forum(): View
    {
        $threads = [
            [
                'id' => 1, 'title' => 'Office Hours This Week', 'tag' => 'General',
                'author' => 'Dr. Maria Fernandez', 'role' => 'Faculty', 'time' => '2 hours ago',
                'content' => 'I will be available for consultation Monday and Wednesday 3–5 PM. Please prepare your questions.',
                'replies' => [
                    ['author' => 'Ana Reyes', 'role' => 'Student', 'time' => '1 hour ago', 'content' => 'Thank you, Professor! I have a question about the upcoming quiz.'],
                    ['author' => 'Miguel Santos', 'role' => 'Student', 'time' => '45 min ago', 'content' => 'Will you be available online as well?'],
                ],
                'reply_count' => 2,
            ],
            [
                'id' => 2, 'title' => 'Mid-term Exam Coverage — MATH301', 'tag' => 'Math',
                'author' => 'Dr. Maria Fernandez', 'role' => 'Faculty', 'time' => '1 day ago',
                'content' => 'The mid-term will cover chapters 3–7. Bring your scientific calculator.',
                'replies' => [
                    ['author' => 'Sofia Cruz', 'role' => 'Student', 'time' => '20 hours ago', 'content' => 'Does chapter 6 include integration by parts?'],
                ],
                'reply_count' => 1,
            ],
        ];

        $stats = ['total_posts' => 12, 'total_replies' => 34, 'my_posts' => 5];
        $tags = ['General', 'Math', 'Physics', 'History', 'Announcement'];

        return view('faculty.forum', compact('threads', 'stats', 'tags'));
    }

    /**
     * Store or update grading criteria for a classroom.
     */
    public function storeGradingCriteria(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeClassroom($classroom);

        $validated = $request->validate([
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*.component_name' => ['required', 'string', 'max:100'],
            'criteria.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'criteria.*.term' => ['required', 'string', 'in:Prelim,Midterm,Final'],
        ]);

        // Validate total weight = 100%
        $totalWeight = collect($validated['criteria'])->sum('weight');
        if (abs($totalWeight - 100) > 0.01) {
            return redirect()->back()->withErrors(['criteria' => 'Total weight must equal exactly 100%. Current total: '.$totalWeight.'%']);
        }

        // Clear existing criteria and replace
        $classroom->gradingCriteria()->delete();

        foreach ($validated['criteria'] as $criterion) {
            $classroom->gradingCriteria()->create([
                'component_name' => $criterion['component_name'],
                'weight' => $criterion['weight'],
                'term' => $criterion['term'],
            ]);
        }

        return redirect()->back()->with('status', 'Grading criteria saved for "'.$classroom->name.'".');
    }

    /**
     * Delete a single grading criterion.
     */
    public function deleteGradingCriteria(Classroom $classroom, \App\Models\ClassroomGradingCriteria $criteria): RedirectResponse
    {
        $this->authorizeClassroom($classroom);

        if ((int) $criteria->classroom_id !== (int) $classroom->id) {
            abort(403);
        }

        $criteria->delete();

        return redirect()->back()->with('status', 'Criterion removed.');
    }

    public function settings(): View
    {
        return view('faculty.settings', [
            'pageDescription' => 'Manage your account security and preferences.',
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                // Requirements: 1 uppercase, 1 lowercase, 1 digit, 1 special char
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/', // At least one special character
            ],
        ], [
            'new_password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'current_password.current_password' => 'The provided password does not match our records.',
        ]);

        $user = $request->user();
        $user->update([
            'password' => $request->new_password,
            'force_password_reset' => false,
        ]);

        \App\Models\AuditTrail::log('Update', 'Security', 'Faculty member updated their password.');

        return back()->with('status', 'Password updated successfully.');
    }

    private function authorizeClassroom(Classroom $classroom): void
    {
        if ((int) $classroom->faculty_user_id !== (int) Auth::id()) {
            abort(403);
        }
    }
}
