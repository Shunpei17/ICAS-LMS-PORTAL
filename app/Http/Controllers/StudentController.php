<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentEnrollmentRequest;
use App\Http\Requests\StoreStudentModuleRecordRequest;
use App\Models\StudentModuleRecord;
use App\Services\GradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DocumentRequest;
use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Support\Facades\DB;
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

        $settings = new \App\Services\SystemSettingsService();
        // All records for this user in the CURRENT term
        $allRecords = StudentModuleRecord::query()
            ->where('user_id', Auth::id())
            ->where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
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

        $settings = new \App\Services\SystemSettingsService();
        StudentModuleRecord::query()->create([
            'user_id' => Auth::id(),
            'module_name' => (string) $module['name'],
            'module_code' => (string) $module['code'],
            'instructor' => (string) $module['instructor'],
            'schedule' => (string) $module['schedule'],
            'academic_year' => $settings->get('academic_year'),
            'semester' => $settings->get('current_semester'),
            'enrollment_status' => 'pending',
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

        $settings = new \App\Services\SystemSettingsService();
        $allRecords = StudentModuleRecord::query()
            ->where('user_id', Auth::id())
            ->where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
            ->orderBy('module_name')
            ->get();

        $records = StudentModuleRecord::query()
            ->where('user_id', Auth::id())
            ->where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
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

        $settings = new \App\Services\SystemSettingsService();
        $record->module_name = (string) $validated['module_name'];
        $record->module_code = $moduleCode;
        $record->academic_year = $settings->get('academic_year');
        $record->semester = $settings->get('current_semester');
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
        $settings = new \App\Services\SystemSettingsService();
        $allRecords = StudentModuleRecord::query()
            ->where('user_id', Auth::id())
            ->where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
            ->whereNotNull('grade_percent')
            ->get();

        $grading = new GradingService;
        $gpas = $allRecords->map(fn($r) => $grading->toGpa((float)$r->grade_percent))
            ->filter(fn($g) => is_string($g) && $g !== 'Dropped')
            ->map(fn($g) => (float)$g);

        $avgGrade = $gpas->count() ? number_format($gpas->avg(), 2) : '0';
        $totalCourses = $allRecords->count();

        $summary = [
            ['label' => 'Overall Average (GPA)', 'value' => $avgGrade],
            ['label' => 'Academic Term', 'value' => $settings->get('academic_year') . ' | ' . $settings->get('current_semester')],
            ['label' => 'Courses', 'value' => (string) $totalCourses],
        ];

        $courses = $allRecords->map(function ($r) {
            return [
                'name' => $r->module_name,
                'description' => 'Academic Code: ' . $r->module_code,
                'grade' => number_format((float)$r->grade_percent, 0) . '%',
                'progress' => (float)$r->grade_percent,
                'quizzes' => [],
            ];
        })->all();

        $majorExams = [];

        return view('student.grades', compact('summary', 'courses', 'majorExams'));
    }

    public function documents(): View
    {
        $userId = Auth::id();
        $allRequests = DocumentRequest::where('user_id', $userId)->latest()->get();

        $summary = [
            ['label' => 'Pending',   'value' => (string) $allRequests->where('status', 'Pending')->count()],
            ['label' => 'Processing', 'value' => (string) $allRequests->where('status', 'Processing')->count()],
            ['label' => 'Completed',  'value' => (string) $allRequests->where('status', 'Completed')->count()],
            ['label' => 'Total',      'value' => (string) $allRequests->count()],
        ];

        $requests = $allRequests->map(function($r) {
            $note = null;
            if ($r->status === 'Completed') {
                $note = 'Ready for pick-up at the Registrar\'s Office.';
            } elseif ($r->status === 'Processing') {
                $note = 'Your request is currently being processed by the registrar.';
            } elseif ($r->status === 'Rejected') {
                $note = 'This request was rejected. Please contact the office for details.';
            }

            return [
                'id' => $r->id,
                'title' => $r->document_type,
                'purpose' => $r->purpose,
                'requested' => $r->created_at->format('M j, Y'),
                'urgency' => $r->urgency,
                'status' => $r->status,
                'note' => $note
            ];
        });

        return view('student.documents', compact('summary', 'requests'));
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'purpose' => 'required|string|max:255',
            'urgency' => 'required|in:Standard,Rush',
        ]);

        DocumentRequest::create([
            'user_id' => Auth::id(),
            'document_type' => $validated['document_type'],
            'purpose' => $validated['purpose'],
            'urgency' => $validated['urgency'],
            'status' => 'Pending',
        ]);

        return back()->with('status', 'Document request submitted successfully.');
    }

    public function forum(): View
    {
        $threads = ForumThread::with(['user', 'replies.user'])
            ->where('is_visible', true)
            ->latest()
            ->paginate(10);

        $topics = ForumThread::select('category', DB::raw('count(*) as count'))
            ->where('is_visible', true)
            ->groupBy('category')
            ->get()
            ->map(fn($t) => ['title' => $t->category, 'count' => $t->count]);

        return view('student.forum', compact('threads', 'topics'));
    }

    public function storeForumThread(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
        ]);

        ForumThread::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
        ]);

        return back()->with('status', 'Discussion posted successfully.');
    }

    public function storeForumReply(Request $request, ForumThread $forumThread): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        ForumReply::create([
            'user_id' => Auth::id(),
            'forum_thread_id' => $forumThread->id,
            'content' => $validated['content'],
        ]);

        return back()->with('status', 'Reply posted successfully.');
    }

    public function reportForumThread(ForumThread $forumThread): RedirectResponse
    {
        $forumThread->update(['is_flagged' => true]);
        return back()->with('status', 'This discussion has been reported and will be reviewed by administrators.');
    }

    public function attendance(): View
    {
        $settings = new \App\Services\SystemSettingsService();
        $recordsRaw = \App\Models\FacultyAttendanceRecord::query()
            ->where('academic_year', $settings->get('academic_year'))
            ->where('semester', $settings->get('current_semester'))
            ->where('student_name', 'like', '%' . Auth::user()->name . '%')
            ->orderByDesc('attendance_date')
            ->get();

        $total = $recordsRaw->count();
        $present = $recordsRaw->where('status', 'Present')->count();
        $absent = $recordsRaw->where('status', 'Absent')->count();
        $late = $recordsRaw->where('status', 'Late')->count();
        $rate = $total > 0 ? round(($present / $total) * 100) : 0;

        $summary = [
            ['label' => 'Total Records', 'value' => (string) $total, 'color' => 'slate'],
            ['label' => 'Present',      'value' => (string) $present, 'color' => 'emerald'],
            ['label' => 'Absent',       'value' => (string) $absent,  'color' => 'rose'],
            ['label' => 'Late',         'value' => (string) $late,    'color' => 'amber'],
            ['label' => 'Attendance Rate', 'value' => $rate . '%', 'color' => 'sky'],
        ];

        $records = $recordsRaw->map(function ($r) {
            return [
                'date' => $r->attendance_date->format('M j, Y'),
                'class' => $r->student_class,
                'course' => $r->student_class, // Subject code
                'faculty' => $r->faculty?->name ?? 'Faculty',
                'status' => $r->status,
            ];
        })->all();

        $courseBreakdown = $recordsRaw->groupBy('student_class')->map(function ($group, $code) {
            return [
                'code' => $code,
                'name' => $code,
                'present' => $group->where('status', 'Present')->count(),
                'absent' => $group->where('status', 'Absent')->count(),
                'late' => $group->where('status', 'Late')->count(),
                'total' => $group->count(),
            ];
        })->values()->all();

        return view('student.attendance', compact('summary', 'records', 'courseBreakdown'));
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
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/'
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

        \App\Models\AuditTrail::log('Update', 'Security', 'Student updated their password.');

        return back()->with('status', 'Password updated successfully.');
    }
}
