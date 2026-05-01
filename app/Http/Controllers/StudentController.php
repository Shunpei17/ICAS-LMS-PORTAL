<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentEnrollmentRequest;
use App\Http\Requests\StoreStudentModuleRecordRequest;
use App\Models\StudentModuleRecord;
use App\Services\GradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function profile(): View
    {
        $user = Auth::user();
        $studentDetails = [
            'student_id' => 'STU-'.str_pad($user->id ?? 999, 4, '0', STR_PAD_LEFT),
            'name' => $user->name ?? 'John Doe',
            'email' => $user->email ?? 'student@example.com',
            'phone' => '+63 912 345 6789',
            'address' => '123 University Ave, Manila, Philippines',
            'program' => $user->course ?? 'Not Specified',
            'year_level' => $user->academic_level ?? 'Not Specified',
            'status' => 'Regular',
            'emergency_contact' => ['name' => 'Jane Doe', 'relation' => 'Mother', 'phone' => '+63 998 765 4321'],
        ];

        return view('student.profile', compact('studentDetails'));
    }

    public function schedule(): View
    {
        $schedule = [
            'Mon' => [
                ['time' => '7:00 AM – 8:30 AM', 'subject' => 'Advanced Mathematics', 'code' => 'MATH301', 'room' => 'Room 201', 'faculty' => 'Prof. Ramos'],
                ['time' => '1:00 PM – 2:30 PM', 'subject' => 'English Composition', 'code' => 'ENG101', 'room' => 'Room 105', 'faculty' => 'Prof. Santos'],
            ],
            'Tue' => [
                ['time' => '9:00 AM – 10:30 AM', 'subject' => 'Physics I', 'code' => 'PHY201', 'room' => 'Lab 3', 'faculty' => 'Prof. Cruz'],
                ['time' => '2:00 PM – 3:30 PM', 'subject' => 'World History', 'code' => 'HIST201', 'room' => 'Room 310', 'faculty' => 'Prof. Dela Rosa'],
            ],
            'Wed' => [
                ['time' => '7:00 AM – 8:30 AM', 'subject' => 'Advanced Mathematics', 'code' => 'MATH301', 'room' => 'Room 201', 'faculty' => 'Prof. Ramos'],
                ['time' => '10:00 AM – 11:30 AM', 'subject' => 'Physical Education', 'code' => 'PE101', 'room' => 'Gymnasium', 'faculty' => 'Coach Villanueva'],
            ],
            'Thu' => [
                ['time' => '9:00 AM – 10:30 AM', 'subject' => 'Physics I', 'code' => 'PHY201', 'room' => 'Lab 3', 'faculty' => 'Prof. Cruz'],
            ],
            'Fri' => [
                ['time' => '7:00 AM – 8:30 AM', 'subject' => 'Advanced Mathematics', 'code' => 'MATH301', 'room' => 'Room 201', 'faculty' => 'Prof. Ramos'],
                ['time' => '1:00 PM – 2:30 PM', 'subject' => 'English Composition', 'code' => 'ENG101', 'room' => 'Room 105', 'faculty' => 'Prof. Santos'],
                ['time' => '3:00 PM – 4:30 PM', 'subject' => 'World History', 'code' => 'HIST201', 'room' => 'Room 310', 'faculty' => 'Prof. Dela Rosa'],
            ],
            'Sat' => [],
        ];
        $totalUnits = 18;
        $totalSubjects = 5;

        return view('student.schedule', compact('schedule', 'totalUnits', 'totalSubjects'));
    }

    public function notifications(): View
    {
        $notifications = [
            ['id' => 1, 'type' => 'grade', 'title' => 'Grade Released: MATH301', 'body' => 'Your grade for Advanced Mathematics Problem Set 3 has been posted. Score: 92/100.', 'time' => '2 hours ago', 'read' => false],
            ['id' => 2, 'type' => 'document', 'title' => 'Document Request Updated', 'body' => 'Your request for Transcript of Records is now being processed. Expected completion: 3 business days.', 'time' => '5 hours ago', 'read' => false],
            ['id' => 3, 'type' => 'announcement', 'title' => 'New Announcement Posted', 'body' => 'ICAS Admin posted: "Final Examination Schedule for AY 2024–2025 Second Semester is now available."', 'time' => '1 day ago', 'read' => false],
            ['id' => 4, 'type' => 'enrollment', 'title' => 'Enrollment Approved', 'body' => 'Your enrollment request for Physics I (PHY201) has been approved by the administrator.', 'time' => '2 days ago', 'read' => true],
            ['id' => 5, 'type' => 'grade', 'title' => 'Grade Released: ENG101', 'body' => 'Your grade for English Composition Draft 1: Descriptive Essay has been posted. Score: 88/100.', 'time' => '3 days ago', 'read' => true],
            ['id' => 6, 'type' => 'forum', 'title' => 'Reply to your post', 'body' => 'Prof. Santos replied to your forum post: "Can we use other citation styles?" — Check the forum for the response.', 'time' => '4 days ago', 'read' => true],
            ['id' => 7, 'type' => 'announcement', 'title' => 'Enrollment Period Reminder', 'body' => 'The enrollment period for Second Semester ends on January 31. Please complete your enrollment now.', 'time' => '1 week ago', 'read' => true],
        ];
        $unreadCount = collect($notifications)->where('read', false)->count();

        return view('student.notifications', compact('notifications', 'unreadCount'));
    }

    public function settings(): View
    {
        $user = Auth::user();

        return view('student.settings', compact('user'));
    }

    public function enrollment(): View
    {
        $catalogByCode = collect($this->enrollmentCatalog())->keyBy('code');

        // All records for this user (including dropped ones)
        $allRecords = StudentModuleRecord::query()
            ->where('user_id', Auth::id())
            ->orderBy('module_name')
            ->get();

        // Active records (not dropped) are treated as enrolled/reserved for availability checks
        $activeRecords = $allRecords->filter(function (StudentModuleRecord $r): bool {
            return $r->enrollment_status !== 'dropped';
        });

        $enrolledCodes = $activeRecords
            ->pluck('module_code')
            ->map(function (?string $moduleCode): string {
                return strtoupper((string) $moduleCode);
            })
            ->all();

        $availableModules = collect($this->enrollmentCatalog())
            ->reject(function (array $module) use ($enrolledCodes): bool {
                return in_array($module['code'], $enrolledCodes, true);
            })
            ->values()
            ->all();

        $enrolledModules = $allRecords
            ->map(function (StudentModuleRecord $record) use ($catalogByCode): array {
                /** @var array<string, mixed>|null $catalogItem */
                $catalogItem = $catalogByCode->get(strtoupper((string) $record->module_code));

                return [
                    'id' => $record->id,
                    'name' => $record->module_name,
                    'code' => strtoupper((string) $record->module_code),
                    'instructor' => $record->instructor ?? ($catalogItem['instructor'] ?? 'Instructor to be announced'),
                    'schedule' => $record->schedule ?? ($catalogItem['schedule'] ?? 'Schedule to be announced'),
                    'units' => $catalogItem['units'] ?? null,
                    'description' => $catalogItem['description'] ?? 'Course details will appear once available.',
                    'enrolled_on' => $record->created_at?->format('M j, Y'),
                    'status' => $record->enrollment_status ?? 'pending',
                    'section' => $record->section,
                ];
            })
            ->values()
            ->all();

        return view('student.enrollment', compact('availableModules', 'enrolledModules'));
    }

    public function storeEnrollment(StoreStudentEnrollmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $moduleCode = strtoupper(trim((string) $validated['module_code']));

        /** @var array<string, mixed>|null $module */
        $module = collect($this->enrollmentCatalog())->firstWhere('code', $moduleCode);

        if ($module === null) {
            return redirect()
                ->route('student.enrollment')
                ->withErrors(['module_code' => 'The selected module is not available for enrollment.'])
                ->withInput();
        }

        StudentModuleRecord::query()->create([
            'user_id' => Auth::id(),
            'module_name' => (string) $module['name'],
            'module_code' => (string) $module['code'],
            'instructor' => (string) $module['instructor'],
            'schedule' => (string) $module['schedule'],
            'section' => null,
            'enrollment_status' => 'pending',
            'grade_percent' => null,
            'documents_count' => 0,
            'upcoming_assessment_title' => null,
            'upcoming_assessment_points' => null,
            'upcoming_assessment_due_date' => null,
            'upcoming_assessment_duration_minutes' => null,
        ]);

        return redirect()
            ->route('student.enrollment')
            ->with('status', 'You are now enrolled in '.(string) $module['name'].' ('.(string) $module['code'].').');
    }

    public function dashboard(Request $request): View
    {
        $filters = [
            'filter_code' => trim((string) $request->query('filter_code', '')),
            'filter_due_from' => trim((string) $request->query('filter_due_from', '')),
            'filter_due_to' => trim((string) $request->query('filter_due_to', '')),
        ];

        $activeFilters = collect($filters)
            ->filter(function (string $value): bool {
                return $value !== '';
            })
            ->all();

        $allRecords = StudentModuleRecord::query()
            ->where('user_id', Auth::id())
            ->orderBy('module_name')
            ->get();

        $records = StudentModuleRecord::query()
            ->where('user_id', Auth::id())
            ->when($filters['filter_code'] !== '', function ($query) use ($filters) {
                $query->where('module_code', 'like', '%'.$filters['filter_code'].'%');
            })
            ->when($filters['filter_due_from'] !== '', function ($query) use ($filters) {
                $query->whereDate('upcoming_assessment_due_date', '>=', $filters['filter_due_from']);
            })
            ->when($filters['filter_due_to'] !== '', function ($query) use ($filters) {
                $query->whereDate('upcoming_assessment_due_date', '<=', $filters['filter_due_to']);
            })
            ->orderBy('module_name')
            ->get();

        $editRecordId = (int) $request->query('edit', 0);
        $editRecord = $editRecordId > 0
            ? StudentModuleRecord::query()
                ->where('id', $editRecordId)
                ->where('user_id', Auth::id())
                ->first()
            : null;

        $grading = new GradingService;
        $gpas = $allRecords->map(function ($r) use ($grading) {
            $g = $grading->toGpa((float) $r->grade_percent);

            return is_string($g) && $g !== 'Dropped' ? (float) $g : null;
        })->filter()->all();

        $averageGrade = count($gpas) ? number_format(array_sum($gpas) / count($gpas), 2) : null;

        $upcomingAssessments = $records
            ->filter(function (StudentModuleRecord $record): bool {
                return filled($record->upcoming_assessment_title)
                    && $record->upcoming_assessment_due_date !== null
                    && $record->upcoming_assessment_due_date->greaterThanOrEqualTo(today());
            })
            ->sortBy('upcoming_assessment_due_date')
            ->values();

        $stats = [
            ['label' => 'Average Grade', 'value' => $averageGrade !== null ? number_format((float) $averageGrade, 0).'%' : 'N/A', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>', 'color' => 'emerald'],
            ['label' => 'Enrolled Courses', 'value' => (string) $allRecords->count(), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>', 'color' => 'sky'],
            ['label' => 'Upcoming Quizzes', 'value' => (string) $upcomingAssessments->count(), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>', 'color' => 'violet'],
            ['label' => 'Documents', 'value' => (string) $allRecords->sum('documents_count'), 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>', 'color' => 'amber'],
        ];

        $courses = $records
            ->map(function (StudentModuleRecord $record): array {
                return [
                    'id' => $record->id,
                    'name' => $record->module_name,
                    'code' => $record->module_code,
                    'instructor' => $record->instructor,
                    'schedule' => $record->schedule ?? 'Schedule to be announced',
                ];
            })
            ->values()
            ->all();

        $assessments = $upcomingAssessments
            ->map(function (StudentModuleRecord $record): array {
                return [
                    'title' => $record->upcoming_assessment_title,
                    'course' => $record->module_name,
                    'points' => $record->upcoming_assessment_points !== null ? $record->upcoming_assessment_points.' pts' : 'TBD',
                    'due' => $record->upcoming_assessment_due_date !== null ? $record->upcoming_assessment_due_date->format('n/j/Y') : 'TBD',
                    'duration' => $record->upcoming_assessment_duration_minutes !== null ? $record->upcoming_assessment_duration_minutes.' min' : 'TBD',
                ];
            })
            ->values()
            ->all();

        return view('student.dashboard', compact('stats', 'courses', 'assessments', 'editRecord', 'filters', 'activeFilters'));
    }

    public function storeModuleRecord(StoreStudentModuleRecordRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $moduleCode = strtoupper(trim((string) $validated['module_code']));

        $recordId = isset($validated['record_id']) ? (int) $validated['record_id'] : 0;

        if ($recordId > 0) {
            $record = StudentModuleRecord::query()
                ->where('id', $recordId)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            $isExistingRecord = true;
        } else {
            $record = new StudentModuleRecord;
            $record->user_id = Auth::id();
            $isExistingRecord = false;
        }

        $record->module_name = (string) $validated['module_name'];
        $record->module_code = $moduleCode;
        $record->instructor = $validated['instructor'] ?? null;
        $record->schedule = $validated['schedule'] ?? null;
        $record->grade_percent = $validated['grade_percent'] ?? null;
        $record->documents_count = $validated['documents_count'] ?? 0;
        $record->upcoming_assessment_title = $validated['upcoming_assessment_title'] ?? null;
        $record->upcoming_assessment_points = $validated['upcoming_assessment_points'] ?? null;
        $record->upcoming_assessment_due_date = $validated['upcoming_assessment_due_date'] ?? null;
        $record->upcoming_assessment_duration_minutes = $validated['upcoming_assessment_duration_minutes'] ?? null;
        $record->save();

        $routeParameters = collect($request->query())
            ->only(['filter_code', 'filter_due_from', 'filter_due_to'])
            ->filter(function (?string $value): bool {
                return $value !== null && $value !== '';
            })
            ->all();

        return redirect()
            ->route('student.dashboard', $routeParameters)
            ->with('status', $isExistingRecord ? 'Module record updated successfully.' : 'Module record added successfully.');
    }

    public function deleteModuleRecord(Request $request, StudentModuleRecord $moduleRecord): RedirectResponse
    {
        if ((int) $moduleRecord->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $moduleRecord->delete();

        $routeParameters = collect($request->query())
            ->only(['filter_code', 'filter_due_from', 'filter_due_to'])
            ->filter(function (?string $value): bool {
                return $value !== null && $value !== '';
            })
            ->all();

        return redirect()
            ->route('student.dashboard', $routeParameters)
            ->with('status', 'Module record deleted successfully.');
    }

    public function dropEnrollment(StudentModuleRecord $moduleRecord): RedirectResponse
    {
        if ((int) $moduleRecord->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $moduleRecord->update(['enrollment_status' => 'dropped']);

        return redirect()
            ->route('student.enrollment')
            ->with('status', 'You have successfully dropped from '.$moduleRecord->module_name.'.');
    }

    /**
     * @return array<int, array{code: string, name: string, instructor: string, schedule: string, units: int, description: string}>
     */
    private function enrollmentCatalog(): array
    {
        return [
            [
                'code' => 'MATH301',
                'name' => 'Advanced Mathematics',
                'instructor' => 'Dr. Maria Fernandez',
                'schedule' => 'Mon, Wed, Fri 9:00 AM',
                'units' => 3,
                'description' => 'Covers advanced algebraic methods, series, and applied problem solving.',
            ],
            [
                'code' => 'PHY201',
                'name' => 'Physics I',
                'instructor' => 'Mr. Paulo Navarro',
                'schedule' => 'Tue, Thu 10:00 AM',
                'units' => 4,
                'description' => 'Introduces mechanics, motion systems, and lab-based scientific reasoning.',
            ],
            [
                'code' => 'HIST201',
                'name' => 'World History',
                'instructor' => 'Mrs. Grace Bautista',
                'schedule' => 'Mon, Wed 2:00 PM',
                'units' => 3,
                'description' => 'Examines key civilizations, global turning points, and historical analysis.',
            ],
            [
                'code' => 'ENG210',
                'name' => 'Academic Writing',
                'instructor' => 'Ms. Angela Villanueva',
                'schedule' => 'Tue, Thu 1:00 PM',
                'units' => 2,
                'description' => 'Builds research writing, argument structure, and citation fundamentals.',
            ],
            [
                'code' => 'CS105',
                'name' => 'Introduction to Programming',
                'instructor' => 'Mr. Noel Garcia',
                'schedule' => 'Mon, Wed, Fri 11:00 AM',
                'units' => 4,
                'description' => 'Develops programming fundamentals with practical coding exercises and projects.',
            ],
            [
                'code' => 'BIO120',
                'name' => 'General Biology',
                'instructor' => 'Dr. Teresa Aquino',
                'schedule' => 'Tue, Thu 3:00 PM',
                'units' => 3,
                'description' => 'Explores living systems, cell structures, and foundational biological processes.',
            ],
        ];
    }

    public function grades(): View
    {
        $summary = [
            ['label' => 'Overall Average', 'value' => '87%'],
            ['label' => 'Average ICAS', 'value' => '35%'],
            ['label' => 'Courses', 'value' => '3'],
        ];

        $courses = [
            ['name' => 'Advanced Mathematics', 'description' => 'ICAS (Internal Continuous Assessment) - 40%', 'grade' => 'A - 90%', 'progress' => 36, 'quizzes' => [
                ['label' => 'Quiz 1', 'score' => '85%'],
                ['label' => 'Quiz 2', 'score' => '90%'],
                ['label' => 'Quiz 3', 'score' => '88%'],
                ['label' => 'Participation', 'score' => '95%'],
            ]],
            ['name' => 'Physics I', 'description' => 'ICAS (Internal Continuous Assessment) - 40%', 'grade' => 'B+ - 83%', 'progress' => 72, 'quizzes' => [
                ['label' => 'Quiz 1', 'score' => '78%'],
                ['label' => 'Quiz 2', 'score' => '82%'],
                ['label' => 'Quiz 3', 'score' => '85%'],
                ['label' => 'Participation', 'score' => '90%'],
            ]],
        ];

        $majorExams = [
            ['label' => 'Midterm', 'value' => '92%'],
            ['label' => 'Final', 'value' => '87%'],
            ['label' => 'Assignment Average', 'value' => '89%'],
        ];

        return view('student.grades', compact('summary', 'courses', 'majorExams'));
    }

    public function documents(): View
    {
        $summary = [
            ['label' => 'Pending', 'value' => '1'],
            ['label' => 'Approved', 'value' => '1'],
            ['label' => 'Ready', 'value' => '0'],
            ['label' => 'Total', 'value' => '2'],
        ];

        $requests = [
            ['title' => 'Transcript', 'purpose' => 'College Application', 'requested' => '3/25/2026', 'status' => 'Approved', 'note' => 'Will be ready in 3-5 business days.'],
            ['title' => 'Certificate of Enrollment', 'purpose' => 'Scholarship Application', 'requested' => '3/28/2026', 'status' => 'Pending', 'note' => null],
        ];

        return view('student.documents', compact('summary', 'requests'));
    }

    public function forum(): View
    {
        $posts = [
            ['author' => 'Miguel Santos', 'role' => 'Student', 'time' => '3 days ago', 'course' => 'Physics I', 'content' => 'Can someone explain the difference between velocity and acceleration?'],
            ['author' => 'Mr. Paulo Navarro', 'role' => 'Faculty', 'time' => '3 days ago', 'course' => 'Physics I', 'content' => 'Great question! Velocity is the rate of change of position, while acceleration is the rate of change of velocity.'],
        ];

        $topics = [
            ['title' => 'Advanced Mathematics', 'count' => 1],
            ['title' => 'Physics I', 'count' => 2],
        ];

        $courses = [
            ['name' => 'Advanced Mathematics', 'code' => 'MATH301'],
            ['name' => 'Physics I', 'code' => 'PHY201'],
            ['name' => 'World History', 'code' => 'HIST201'],
        ];

        return view('student.forum', compact('posts', 'topics', 'courses'));
    }

    public function attendance(): View
    {
        $summary = [
            ['label' => 'Total Days',  'value' => '45', 'color' => 'slate'],
            ['label' => 'Present',     'value' => '38', 'color' => 'emerald'],
            ['label' => 'Absent',      'value' => '4',  'color' => 'rose'],
            ['label' => 'Late',        'value' => '3',  'color' => 'amber'],
            ['label' => 'Attendance Rate', 'value' => '84%', 'color' => 'sky'],
        ];

        $records = [
            ['date' => 'Apr 21, 2026', 'class' => 'MATH301', 'course' => 'Advanced Mathematics',    'faculty' => 'Dr. Maria Fernandez', 'status' => 'Present'],
            ['date' => 'Apr 21, 2026', 'class' => 'PHY201',  'course' => 'Physics I',               'faculty' => 'Mr. Paulo Navarro',   'status' => 'Present'],
            ['date' => 'Apr 20, 2026', 'class' => 'HIST201', 'course' => 'World History',           'faculty' => 'Mrs. Grace Bautista', 'status' => 'Late'],
            ['date' => 'Apr 20, 2026', 'class' => 'MATH301', 'course' => 'Advanced Mathematics',    'faculty' => 'Dr. Maria Fernandez', 'status' => 'Present'],
            ['date' => 'Apr 18, 2026', 'class' => 'PHY201',  'course' => 'Physics I',               'faculty' => 'Mr. Paulo Navarro',   'status' => 'Absent'],
            ['date' => 'Apr 18, 2026', 'class' => 'HIST201', 'course' => 'World History',           'faculty' => 'Mrs. Grace Bautista', 'status' => 'Present'],
            ['date' => 'Apr 17, 2026', 'class' => 'MATH301', 'course' => 'Advanced Mathematics',    'faculty' => 'Dr. Maria Fernandez', 'status' => 'Present'],
            ['date' => 'Apr 16, 2026', 'class' => 'PHY201',  'course' => 'Physics I',               'faculty' => 'Mr. Paulo Navarro',   'status' => 'Present'],
            ['date' => 'Apr 15, 2026', 'class' => 'HIST201', 'course' => 'World History',           'faculty' => 'Mrs. Grace Bautista', 'status' => 'Absent'],
            ['date' => 'Apr 14, 2026', 'class' => 'MATH301', 'course' => 'Advanced Mathematics',    'faculty' => 'Dr. Maria Fernandez', 'status' => 'Late'],
        ];

        $courseBreakdown = [
            ['code' => 'MATH301', 'name' => 'Advanced Mathematics', 'present' => 14, 'absent' => 1, 'late' => 1, 'total' => 16],
            ['code' => 'PHY201',  'name' => 'Physics I',            'present' => 13, 'absent' => 2, 'late' => 1, 'total' => 16],
            ['code' => 'HIST201', 'name' => 'World History',        'present' => 11, 'absent' => 1, 'late' => 1, 'total' => 13],
        ];

        return view('student.attendance', compact('summary', 'records', 'courseBreakdown'));
    }
}
