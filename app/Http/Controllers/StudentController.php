<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentEnrollmentRequest;
use App\Http\Requests\StoreStudentModuleRecordRequest;
use App\Models\StudentModuleRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function enrollment(): View
    {
        $catalogByCode = collect($this->enrollmentCatalog())->keyBy('code');

        $enrolledRecords = StudentModuleRecord::query()
            ->where('user_id', Auth::id())
            ->orderBy('module_name')
            ->get();

        $enrolledCodes = $enrolledRecords
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

        $enrolledModules = $enrolledRecords
            ->map(function (StudentModuleRecord $record) use ($catalogByCode): array {
                /** @var array<string, mixed>|null $catalogItem */
                $catalogItem = $catalogByCode->get(strtoupper((string) $record->module_code));

                return [
                    'name' => $record->module_name,
                    'code' => strtoupper((string) $record->module_code),
                    'instructor' => $record->instructor ?? ($catalogItem['instructor'] ?? 'Instructor to be announced'),
                    'schedule' => $record->schedule ?? ($catalogItem['schedule'] ?? 'Schedule to be announced'),
                    'units' => $catalogItem['units'] ?? null,
                    'description' => $catalogItem['description'] ?? 'Course details will appear once available.',
                    'enrolled_on' => $record->created_at?->format('M j, Y'),
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

        $averageGrade = $allRecords->avg('grade_percent');

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

    public function classrooms(): View
    {
        $classrooms = [
            ['name' => 'Advanced Mathematics', 'code' => 'MATH301', 'instructor' => 'Dr. Maria Fernandez', 'schedule' => 'Mon, Wed, Fri 9:00 AM', 'quizzes' => 1],
            ['name' => 'Physics I', 'code' => 'PHY201', 'instructor' => 'Mr. Paulo Navarro', 'schedule' => 'Tue, Thu 10:00 AM', 'quizzes' => 1],
            ['name' => 'World History', 'code' => 'HIST201', 'instructor' => 'Mrs. Grace Bautista', 'schedule' => 'Mon, Wed 2:00 PM', 'quizzes' => 0],
        ];

        return view('student.classrooms', compact('classrooms'));
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
}
