<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Http\Requests\ImportStudentCsvRequest;
use App\Models\Announcement;
use App\Models\AuditTrail;
use App\Models\Classroom;
use App\Models\DocumentRequest;
use App\Models\FacultyAttendanceRecord;
use App\Models\StudentModuleRecord;
use App\Models\User;
use App\Services\StudentBulkImportService;
use App\Services\SystemSettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ForumThread;
use App\Models\ForumReply;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        // Dynamic dashboard metrics
        $totalUsers = User::count();
        $activeTeachers = User::where('role', 'faculty')->where('status', 'active')->count();
        $activeStudents = User::where('role', 'student')->where('status', 'active')->count();
        $pendingRequests = DocumentRequest::where('status', 'Pending')->count();

        $summary = [
            ['label' => 'Total Users', 'value' => (string) $totalUsers],
            ['label' => 'Active Teachers', 'value' => (string) $activeTeachers],
            ['label' => 'Active Students', 'value' => (string) $activeStudents],
            ['label' => 'Pending Requests', 'value' => (string) $pendingRequests],
        ];

        $totalEnrollments = StudentModuleRecord::count();
        $totalClassrooms = Classroom::count();
        $totalAttendanceRecords = FacultyAttendanceRecord::count();
        $totalAnnouncements = Announcement::count();

        $overview = [
            ['title' => 'Total Enrollments', 'value' => (string) $totalEnrollments],
            ['title' => 'Active Classrooms', 'value' => (string) $totalClassrooms],
            ['title' => 'Attendance Records', 'value' => (string) $totalAttendanceRecords],
            ['title' => 'Total Announcements', 'value' => (string) $totalAnnouncements],
        ];

        $recentActions = [
            ['title' => 'Total Registered Users', 'subtitle' => $totalUsers.' users in the system'],
            ['title' => 'Active Courses', 'subtitle' => 'System is running smoothly'],
            ['title' => 'System Health', 'subtitle' => 'All systems operational'],
        ];

        $pendingUsersCount = User::where('status', 'pending')->count();

        // Live analytics for dashboard
        $levelStats = collect(['Senior High School', '1st Year College', '2nd Year College', '3rd Year College'])
            ->map(fn ($level) => ['label' => $level, 'count' => User::where('role', 'student')->where('academic_level', $level)->count()])
            ->all();

        $strandStats = [
            'ICT' => User::where('role', 'student')->where('academic_level', 'Senior High School')->where('strand', 'ICT')->count(),
            'HE' => User::where('role', 'student')->where('academic_level', 'Senior High School')->where('strand', 'HE')->count(),
        ];

        $courseStats = User::where('role', 'student')
            ->whereNotNull('course')
            ->where('course', '!=', '')
            ->select('course as label', DB::raw('count(*) as count'))
            ->groupBy('course')
            ->get()
            ->all();

        return view('admin.dashboard', compact(
            'summary',
            'overview',
            'recentActions',
            'pendingUsersCount',
            'levelStats',
            'strandStats',
            'courseStats'
        ));
    }

    public function users(): View
    {
        $roleFilter = request('role', '');
        $statusFilter = request('status', '');
        $search = request('search', '');

        $query = User::query()
            ->when($roleFilter, fn ($q) => $q->where('role', $roleFilter))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            }));

        $filtered = $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'enrollment_type' => $user->enrollment_type,
                'receipt_proof' => $user->receipt_proof,
                'student_id_proof' => $user->student_id_proof,
                'joined' => $user->created_at ? $user->created_at->format('M j, Y') : 'N/A',
            ];
        })->all();

        $stats = [
            'total' => User::count(),
            'students' => User::where('role', 'student')->count(),
            'faculty' => User::where('role', 'faculty')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'pending' => User::where('status', 'pending')->count(),
        ];

        return view('admin.users', compact('filtered', 'stats', 'roleFilter', 'statusFilter', 'search'));
    }

    public function toggleUserStatus(Request $request, User $user): RedirectResponse
    {
        $status = $request->input('status');
        
        if (!$status) {
            $status = ($user->status === 'active') ? 'inactive' : 'active';
        }

        $user->update(['status' => $status]);

        AuditTrail::log('Update', 'Users', 'Admin updated status of ' . $user->name . ' to ' . $status);

        return back()->with('status', "User {$user->name} has been updated to {$status}.");
    }

    public function downloadStudentTemplate(): StreamedResponse
    {
        $filename = 'student-import-template-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            $headers = ['Student Number', 'Full Name', 'Email', 'Academic Level', 'Course', 'Strand'];
            fputcsv($output, $headers);

            $examples = [
                ['STU-001', 'Juan Dela Cruz', 'juan.delacruz@school.edu', '1st Year College', 'BSIT', ''],
                ['STU-002', 'Maria Santos', 'maria.santos@school.edu', '2nd Year College', 'BSHM', ''],
                ['STU-003', 'Carlos Reyes', 'carlos.reyes@school.edu', 'Senior High School', '', 'STEM'],
            ];
            foreach ($examples as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadAdminTemplate(): StreamedResponse
    {
        $filename = 'admin-import-template-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            $headers = ['Admin unique number', 'Full Name', 'Email', 'Department'];
            fputcsv($output, $headers);

            $examples = [
                ['ADM-001', 'System Admin One', 'admin.one@school.edu', 'IT Department'],
                ['ADM-002', 'Registrar Admin', 'registrar@school.edu', 'Registrar'],
            ];
            foreach ($examples as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadFacultyTemplate(): StreamedResponse
    {
        $filename = 'faculty-import-template-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            $headers = ['Faculty Unique Number', 'Full Name', 'Email', 'Department'];
            fputcsv($output, $headers);

            $examples = [
                ['FAC-001', 'Dr. Maria Fernandez', 'maria.fernandez@school.edu', 'CAS'],
                ['FAC-002', 'Prof. Juan Santos', 'juan.santos@school.edu', 'CBA'],
            ];
            foreach ($examples as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importUsers(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $service = new StudentBulkImportService;
        $result = $service->import($request->file('csv_file'));

        $message = "Import complete: {$result['success']} created, {$result['failed']} failed, {$result['duplicates']} duplicates.";

        if (! empty($result['errors'])) {
            $errorSummary = implode("\n", array_slice($result['errors'], 0, 10));
            if (count($result['errors']) > 10) {
                $errorSummary .= "\n... and ".(count($result['errors']) - 10).' more errors.';
            }

            return back()
                ->with('status', $message)
                ->withErrors(['csv_errors' => $errorSummary]);
        }

        AuditTrail::log('Create', 'Users', 'Admin imported users via CSV: ' . $result['success'] . ' successful');

        return back()->with('status', $message);
    }

    public function showUser(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function editUser(Request $request, User $user): View|RedirectResponse
    {
        if ($request->method() === 'GET') {
            return view('admin.users.edit', compact('user'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,faculty,admin',
            'academic_level' => 'nullable|string',
            'course' => 'nullable|string|max:255',
            'strand' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users')->with('status', "User {$user->name} updated successfully.");
    }

    public function deleteUser(User $user): RedirectResponse
    {
        $name = $user->name;
        $user->delete();

        AuditTrail::log('Delete', 'Users', 'Admin permanently deleted user: ' . $name);

        return redirect()->route('admin.users')->with('status', "User {$name} has been permanently deleted.");
    }

    public function settings(): View
    {
        $settings = new SystemSettingsService;

        $schoolSettings = [
            'school_name' => 'INFOTECH COLLEGE OF ARTS AND SCIENCES - MARCOS HIGHWAY',
            'academic_year' => $settings->get('academic_year', '2024–2025'),
            'semester' => $settings->get('current_semester', 'Second Semester'),
            'enrollment_start' => $settings->get('enrollment_start', '2025-01-06'),
            'enrollment_end' => $settings->get('enrollment_end', '2025-01-31'),
            'exam_start' => $settings->get('final_exam_start', '2025-03-17'),
            'timezone' => $settings->get('timezone', 'Asia/Manila'),
            'default_passing_grade' => (int) $settings->get('passing_grade', 75),
            'grading_scale' => $settings->get('grading_scale', 'gpa'),
            'grade_equivalency' => $settings->get('grade_equivalency', [
                ['range' => '99-100', 'gpa' => '1.00'],
                ['range' => '96-98', 'gpa' => '1.25'],
                ['range' => '93-95', 'gpa' => '1.50'],
                ['range' => '90-92', 'gpa' => '1.75'],
                ['range' => '87-89', 'gpa' => '2.00'],
                ['range' => '84-86', 'gpa' => '2.25'],
                ['range' => '81-83', 'gpa' => '2.50'],
                ['range' => '78-80', 'gpa' => '2.75'],
                ['range' => '75-77', 'gpa' => '3.00'],
                ['range' => '0-50', 'gpa' => 'Dropped'],
            ]),
            'theme_admin_color' => $settings->get('theme_admin_color', '#16a34a'),
            'theme_faculty_color' => $settings->get('theme_faculty_color', '#f59e0b'),
            'theme_student_color' => $settings->get('theme_student_color', '#7c3aed'),
        ];

        return view('admin.settings', compact('schoolSettings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_year' => 'nullable|string|max:50',
            'current_semester' => 'nullable|string|max:50',
            'enrollment_start' => 'nullable|date',
            'enrollment_end' => 'nullable|date',
            'final_exam_start' => 'nullable|date',
            'timezone' => 'nullable|string|max:100',
            'grading_scale' => 'nullable|string|max:30',
            'theme_admin_color' => 'nullable|string|max:30',
            'theme_faculty_color' => 'nullable|string|max:30',
            'theme_student_color' => 'nullable|string|max:30',
        ]);

        // Force the passing grade to the institutional constant — never accept user input
        $data['passing_grade'] = \App\Services\GradingService::PASSING_GRADE;

        $settings = new SystemSettingsService;
        foreach ($data as $k => $v) {
            if ($v === null) {
                continue;
            }
            // Save mapping differences
            $key = $k === 'current_semester' ? 'current_semester' : $k;
            $settings->set($key, $v);
        }

        return back()->with('status', 'Settings updated. Global term and appearance updated.');
    }

    public function attendance(Request $request): View
    {
        $filters = $this->resolveAttendanceFilters($request);
        $activeFilters = collect($filters)
            ->filter(function (string $value): bool {
                return $value !== '';
            })
            ->all();

        $baseQuery = $this->queryAttendanceRecords($filters, $request->has('history'));

        $totalRecords = (clone $baseQuery)->count();
        $presentRecords = (clone $baseQuery)->where('status', 'Present')->count();
        $absentRecords = (clone $baseQuery)->where('status', 'Absent')->count();
        $lateRecords = (clone $baseQuery)->where('status', 'Late')->count();
        $attendanceRate = $totalRecords > 0
            ? (string) round(($presentRecords / $totalRecords) * 100).'%'
            : '0%';

        $summary = [
            ['label' => 'Total Records', 'value' => (string) $totalRecords],
            ['label' => 'Present', 'value' => (string) $presentRecords],
            ['label' => 'Absent', 'value' => (string) $absentRecords],
            ['label' => 'Late', 'value' => (string) $lateRecords],
            ['label' => 'Attendance Rate', 'value' => $attendanceRate],
        ];

        $records = (clone $baseQuery)
            ->with(['faculty:id,name','studentUser:id,course,academic_level'])
            ->orderByDesc('attendance_date')
            ->orderBy('student_name')
            ->paginate(12)
            ->withQueryString();

        $courseOptions = User::query()
            ->where('role', 'student')
            ->whereNotNull('course')
            ->select('course')
            ->distinct()
            ->orderBy('course')
            ->pluck('course')
            ->all();

        $facultyOptions = User::query()
            ->where('role', 'faculty')
            ->whereHas('facultyAttendanceRecords')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (User $faculty): array {
                return [
                    'id' => $faculty->id,
                    'name' => $faculty->name,
                ];
            })
            ->all();

        return view('admin.attendance', compact('summary', 'records', 'filters', 'activeFilters', 'courseOptions', 'facultyOptions'));
    }

    public function exportAttendance(Request $request)
    {
        $filters = $this->resolveAttendanceFilters($request);

        $baseQuery = $this->queryAttendanceRecords($filters)->orderByDesc('attendance_date')->orderBy('student_name');

        $records = $baseQuery->get()->map(function ($r) {
            return [
                'student_name' => $r->student_name,
                'student_course' => $r->studentUser->course ?? $r->student_course ?? '',
                'student_academic_level' => $r->studentUser->academic_level ?? $r->student_academic_level ?? '',
                'faculty' => $r->faculty?->name ?? '',
                'attendance_date' => $r->attendance_date?->format('Y-m-d') ?? '',
                'status' => $r->status,
                'notes' => $r->notes ?? '',
            ];
        });

        $format = $request->query('format', 'csv');
        $filenameBase = 'attendance-'.now()->format('Ymd-His');

        if ($format === 'xlsx') {
            return Excel::download(new AttendanceExport(collect($records)), $filenameBase.'.xlsx');
        }

        if ($format === 'pdf') {
            return Pdf::loadView('admin.exports.attendance', ['records' => $records])->download($filenameBase.'.pdf');
        }

        $filename = $filenameBase.'.csv';

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Student Name', 'Course', 'Academic Level', 'Faculty', 'Date', 'Status', 'Notes']);
            foreach ($records as $row) {
                fputcsv($out, array_values((array) $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function grades(Request $request): View
    {
        $statusFilter = $request->query('status', '');
        $subjectFilter = $request->query('subject', '');
        $academicLevelFilter = $request->query('academic_level', '');
        $courseFilter = $request->query('course', '');
        $strandFilter = $request->query('strand', '');

        $coursesData = StudentModuleRecord::query()
            ->when(! $request->has('history'), function ($q) {
                $settings = new SystemSettingsService();
                $q->where('academic_year', $settings->get('academic_year'))
                  ->where('semester', $settings->get('current_semester'));
            })
            ->when($statusFilter !== '', function ($q) use ($statusFilter) {
                if ($statusFilter === 'Pending') {
                    $q->where('grade_verified', false);
                } elseif ($statusFilter === 'Verified') {
                    $q->where('grade_verified', true);
                }
            })
            ->when($subjectFilter !== '', function ($query) use ($subjectFilter) {
                return $query->where('module_code', $subjectFilter);
            })
            ->when($academicLevelFilter !== '' || $courseFilter !== '' || $strandFilter !== '', function ($query) use ($academicLevelFilter, $courseFilter, $strandFilter) {
                $query->whereHas('user', function ($q) use ($academicLevelFilter, $courseFilter, $strandFilter) {
                    if ($academicLevelFilter !== '') {
                        $q->where('academic_level', $academicLevelFilter);
                    }
                    if ($courseFilter !== '') {
                        $q->where('course', $courseFilter);
                    }
                    if ($strandFilter !== '') {
                        $q->where('strand', $strandFilter);
                    }
                });
            })
            ->get()
            ->groupBy('module_code');

        $courses = [];
        foreach ($coursesData as $code => $records) {
            $avg = $records->avg('grade_percent');
            $highest = $records->max('grade_percent');
            $lowest = $records->min('grade_percent');
            $passing = $records->count() > 0 ? ($records->where('grade_percent', '>=', 75)->count() / $records->count() * 100) : 0;

            $dist = [
                'A' => $records->where('grade_percent', '>=', 90)->count(),
                'B' => $records->whereBetween('grade_percent', [80, 89.99])->count(),
                'C' => $records->whereBetween('grade_percent', [75, 79.99])->count(),
                'D' => $records->whereBetween('grade_percent', [70, 74.99])->count(),
                'F' => $records->where('grade_percent', '<', 70)->count(),
            ];

            $courses[] = [
                'name' => $records->first()->module_name,
                'code' => $code,
                'avg' => round($avg),
                'highest' => round($highest),
                'lowest' => round($lowest),
                'passing' => round($passing),
                'dist' => $dist,
            ];
        }

        $allGradesQuery = StudentModuleRecord::query()
            ->with(['user:id,name,academic_level,course'])
            ->whereNotNull('grade_percent')
            ->when(! $request->has('history'), function ($q) {
                $settings = new SystemSettingsService();
                $q->where('academic_year', $settings->get('academic_year'))
                  ->where('semester', $settings->get('current_semester'));
            })
            ->when($statusFilter !== '', function ($q) use ($statusFilter) {
                if ($statusFilter === 'Pending') {
                    $q->where('grade_verified', false);
                } elseif ($statusFilter === 'Verified') {
                    $q->where('grade_verified', true);
                }
            })
            ->when($subjectFilter !== '', function ($query) use ($subjectFilter) {
                return $query->where('module_code', $subjectFilter);
            })
            ->when($academicLevelFilter !== '' || $courseFilter !== '' || $strandFilter !== '', function ($query) use ($academicLevelFilter, $courseFilter, $strandFilter) {
                $query->whereHas('user', function ($q) use ($academicLevelFilter, $courseFilter, $strandFilter) {
                    if ($academicLevelFilter !== '') {
                        $q->where('academic_level', $academicLevelFilter);
                    }
                    if ($courseFilter !== '') {
                        $q->where('course', $courseFilter);
                    }
                    if ($strandFilter !== '') {
                        $q->where('strand', $strandFilter);
                    }
                });
            });

        // All grades for the consolidated admin table (supports status filtering)
        $allGrades = $allGradesQuery->paginate(15)->withQueryString();

        $subjectOptions = StudentModuleRecord::query()
            ->select('module_code', 'module_name')
            ->distinct()
            ->orderBy('module_name')
            ->get()
            ->map(fn ($r) => ['code' => $r->module_code, 'name' => $r->module_name])
            ->all();

        // Compute overview metrics from the filtered grades set
        $allFiltered = (clone $allGradesQuery)->get();
        $overallAverage = $allFiltered->count() ? round($allFiltered->avg('grade_percent'), 1) : 0;
        $passingRate = $allFiltered->count() ? round(($allFiltered->where('grade_percent', '>=', 75)->count() / $allFiltered->count()) * 100) : 0;
        $studentsGraded = $allFiltered->pluck('user_id')->unique()->count();
        $strandsMonitored = $allFiltered->pluck('user.strand')->filter()->unique()->count();

        $overview = [
            ['label' => 'Overall Average', 'value' => $overallAverage.'%', 'color' => 'emerald'],
            ['label' => 'Passing Rate', 'value' => $passingRate.'%', 'color' => 'sky'],
            ['label' => 'Students Graded', 'value' => (string) $studentsGraded, 'color' => 'slate'],
            ['label' => 'Strands Monitored', 'value' => (string) $strandsMonitored, 'color' => 'slate'],
        ];

        return view('admin.grades', compact('courses', 'allGrades', 'subjectFilter', 'subjectOptions', 'academicLevelFilter', 'courseFilter', 'strandFilter', 'overview', 'statusFilter'));
    }

    public function verifyGrade(StudentModuleRecord $moduleRecord): RedirectResponse
    {
        $moduleRecord->update(['grade_verified' => true]);

        return back()->with('status', 'Grade verified for '.($moduleRecord->user->name ?? 'Student'));
    }

    public function exportGrades(Request $request): StreamedResponse
    {
        $subjectFilter = $request->query('subject');
        $academicLevelFilter = $request->query('academic_level');
        $courseFilter = $request->query('course');
        $strandFilter = $request->query('strand');

        $records = StudentModuleRecord::query()
            ->with(['user:id,name,email,academic_level,course,strand'])
            ->whereNotNull('grade_percent')
            ->when(! $request->has('history'), function ($q) {
                $settings = new SystemSettingsService();
                $q->where('academic_year', $settings->get('academic_year'))
                  ->where('semester', $settings->get('current_semester'));
            })
            ->when($subjectFilter, function ($query, $subjectFilter) {
                return $query->where('module_code', $subjectFilter);
            })
            ->when($academicLevelFilter !== '' || $courseFilter !== '' || $strandFilter !== '', function ($query) use ($academicLevelFilter, $courseFilter, $strandFilter) {
                $query->whereHas('user', function ($q) use ($academicLevelFilter, $courseFilter, $strandFilter) {
                    if ($academicLevelFilter !== null && $academicLevelFilter !== '') {
                        $q->where('academic_level', $academicLevelFilter);
                    }
                    if ($courseFilter !== null && $courseFilter !== '') {
                        $q->where('course', $courseFilter);
                    }
                    if ($strandFilter !== null && $strandFilter !== '') {
                        $q->where('strand', $strandFilter);
                    }
                });
            })
            ->orderBy('module_name')
            ->orderBy('module_code')
            ->get();

        $filename = 'grade-generator-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($records): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Student Name', 'Student Email', 'Course/Strand', 'Level', 'Module Name', 'Module Code', 'Instructor', 'Grade (%)', 'GPA Equivalent']);

            $gradingService = new \App\Services\GradingService();

            foreach ($records as $record) {
                $raw = (float) $record->grade_percent;
                $gpa = $gradingService->toGpa($raw) ?? 'N/A';

                $courseStrand = str_contains($record->user?->academic_level ?? '', 'Senior High School')
                    ? ($record->user?->strand ?? 'N/A')
                    : ($record->user?->course ?? 'N/A');

                fputcsv($output, [
                    $record->user?->name ?? 'Unknown Student',
                    $record->user?->email ?? '',
                    $courseStrand,
                    $record->user?->academic_level ?? '',
                    $record->module_name,
                    $record->module_code,
                    $record->instructor ?? '',
                    number_format($raw, 2),
                    $gpa,
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function updateGrade(Request $request, StudentModuleRecord $moduleRecord): RedirectResponse
    {
        $validated = $request->validate([
            'grade_percent' => 'required|numeric|min:0|max:100',
            'reason' => 'required|string|max:500',
        ]);

        $oldGrade = $moduleRecord->grade_percent;
        $newGrade = $validated['grade_percent'];
        $reason = $validated['reason'];

        $moduleRecord->update([
            'grade_percent' => $newGrade,
            'grade_verified' => true,
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'Update Grade',
            'module' => 'Grades',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detail' => 'Admin '.auth()->user()->name.' manually changed grade for '.($moduleRecord->user->name ?? 'Student').' in '.$moduleRecord->module_name." from {$oldGrade} to {$newGrade}. Reason: {$reason}",
        ]);

        return back()->with('status', 'Grade updated and logged successfully.');
    }

    public function documents(Request $request): View
    {
        $search = $request->query('search');
        $type = $request->query('type');
        $status = $request->query('status');

        $requestsQuery = DocumentRequest::with('user:id,name')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($q) use ($type) {
                $q->where('document_type', $type);
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest();

        $requests = $requestsQuery->get()->map(function ($doc) {
            return [
                'id' => $doc->id,
                'student' => $doc->user->name ?? 'Unknown',
                'doc' => $doc->document_type,
                'purpose' => $doc->purpose ?? 'N/A',
                'date' => $doc->created_at->format('M j'),
                'urgency' => $doc->urgency,
                'status' => $doc->status,
            ];
        })->all();

        $pending = DocumentRequest::where('status', 'Pending')->count();
        $processing = DocumentRequest::where('status', 'Processing')->count();
        $completed = DocumentRequest::where('status', 'Completed')->count();
        $rejected = DocumentRequest::where('status', 'Rejected')->count();
        $total = DocumentRequest::count();

        $summary = [
            ['label' => 'Pending', 'value' => (string) $pending, 'color' => 'amber'],
            ['label' => 'Processing', 'value' => (string) $processing, 'color' => 'sky'],
            ['label' => 'Completed', 'value' => (string) $completed, 'color' => 'emerald'],
            ['label' => 'Rejected', 'value' => (string) $rejected, 'color' => 'rose'],
        ];

        return view('admin.documents', compact('requests', 'search', 'type', 'status', 'summary'));
    }

    public function updateDocument(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Pending,Processing,Completed,Rejected'],
        ]);

        $documentRequest->update(['status' => $validated['status']]);

        return back()->with('status', 'Document request status updated to ' . $validated['status']);
    }

    public function deleteDocument(DocumentRequest $documentRequest): RedirectResponse
    {
        $name = $documentRequest->user->name ?? 'Request';
        $documentRequest->delete();

        return back()->with('status', 'Document request for ' . $name . ' has been permanently deleted.');
    }

    public function forum(): View
    {
        $threads = ForumThread::with(['user', 'replies'])
            ->latest()
            ->paginate(15);

        $totalPosts = ForumThread::count();
        $totalReplies = ForumReply::count();
        $flagged = ForumThread::where('is_flagged', true)->count();

        $stats = [
            ['label' => 'Total Posts', 'value' => (string) $totalPosts, 'color' => 'slate'],
            ['label' => 'Total Replies', 'value' => (string) $totalReplies, 'color' => 'slate'],
            ['label' => 'Flagged Analytics', 'value' => (string) $flagged, 'color' => 'rose'],
        ];

        return view('admin.forum', compact('threads', 'stats'));
    }

    public function showForumThread(ForumThread $forumThread): View
    {
        $forumThread->load(['user', 'replies.user']);
        return view('admin.forum-show', compact('forumThread'));
    }

    public function toggleHideForumThread(ForumThread $forumThread): RedirectResponse
    {
        $forumThread->update(['is_visible' => ! $forumThread->is_visible]);
        $status = $forumThread->is_visible ? 'visible' : 'hidden';
        return back()->with('status', "Post is now {$status}.");
    }

    public function flagForumThread(ForumThread $forumThread): RedirectResponse
    {
        $forumThread->update(['is_flagged' => true]);
        return back()->with('status', 'Post has been flagged for review.');
    }

    public function deleteForumThread(ForumThread $forumThread): RedirectResponse
    {
        $forumThread->delete();
        return back()->with('status', 'Post and all associated replies have been permanently deleted.');
    }

    /**
    * @return array{search: string, status: string, faculty_user_id: string, academic_level: string, course: string, from_date: string, to_date: string}
     */
    private function resolveAttendanceFilters(Request $request): array
    {
        $status = trim((string) $request->query('status', ''));

        if (! in_array($status, ['Present', 'Absent', 'Late'], true)) {
            $status = '';
        }

        $facultyUserId = trim((string) $request->query('faculty_user_id', ''));
        $academicLevel = trim((string) $request->query('academic_level', ''));
        $course = trim((string) $request->query('course', ''));

        if ($facultyUserId !== '' && ! ctype_digit($facultyUserId)) {
            $facultyUserId = '';
        }

        return [
            'search' => trim((string) $request->query('search', '')),
            'status' => $status,
            'faculty_user_id' => $facultyUserId,
            'academic_level' => $academicLevel,
            'course' => $course,
            'from_date' => trim((string) $request->query('from_date', '')),
            'to_date' => trim((string) $request->query('to_date', '')),
        ];
    }

    /**
     * @param  array{search: string, status: string, faculty_user_id: string, academic_level: string, course: string, from_date: string, to_date: string}  $filters
     */
    private function queryAttendanceRecords(array $filters, bool $showHistory = false): Builder
    {
        return FacultyAttendanceRecord::query()
            ->when(! $showHistory, function ($q) {
                $settings = new SystemSettingsService();
                $q->where('academic_year', $settings->get('academic_year'))
                  ->where('semester', $settings->get('current_semester'));
            })
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->where('student_name', 'like', '%'.$filters['search'].'%');
            })
            ->when($filters['academic_level'] !== '', function (Builder $query) use ($filters): void {
                $level = $filters['academic_level'];
                $query->whereExists(function ($q) use ($level) {
                    $q->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.name', 'faculty_attendance_records.student_name')
                        ->where('users.role', 'student')
                        ->where('users.academic_level', $level);
                });
            })
            ->when($filters['course'] !== '', function (Builder $query) use ($filters): void {
                $course = $filters['course'];
                $query->whereExists(function ($q) use ($course) {
                    $q->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.name', 'faculty_attendance_records.student_name')
                        ->where('users.role', 'student')
                        ->where('users.course', $course);
                });
            })
            ->when($filters['status'] !== '', function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            
            ->when($filters['faculty_user_id'] !== '', function (Builder $query) use ($filters): void {
                $query->where('faculty_user_id', (int) $filters['faculty_user_id']);
            })
            ->when($filters['from_date'] !== '', function (Builder $query) use ($filters): void {
                $query->whereDate('attendance_date', '>=', $filters['from_date']);
            })
            ->when($filters['to_date'] !== '', function (Builder $query) use ($filters): void {
                $query->whereDate('attendance_date', '<=', $filters['to_date']);
            });
    }

    private function resolveGradeStatus(float $averageGrade): string
    {
        if ($averageGrade >= 85) {
            return 'On track';
        }

        if ($averageGrade >= 75) {
            return 'Needs review';
        }

        return 'At risk';
    }

    public function enrollments(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['pending', 'enrolled', 'dropped'], true)
            ? $request->query('tab')
            : 'pending';

        $courseFilter = trim((string) $request->query('course', ''));

        $enrollments = StudentModuleRecord::query()
            ->where('enrollment_status', $tab === 'pending' ? 'faculty_approved' : $tab)
            ->when(! $request->has('history'), function ($q) {
                $settings = new SystemSettingsService();
                $q->where('academic_year', $settings->get('academic_year'))
                  ->where('semester', $settings->get('current_semester'));
            })
            ->with(['user:id,name,email'])
            ->when($courseFilter !== '', function ($query) use ($courseFilter): void {
                $query->where('module_code', $courseFilter);
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $enrolledCount = StudentModuleRecord::where('enrollment_status', 'enrolled')->count();
        $pendingCount = StudentModuleRecord::where('enrollment_status', 'faculty_approved')->count();
        $droppedCount = StudentModuleRecord::where('enrollment_status', 'dropped')->count();

        $summary = [
            ['label' => 'Pending', 'value' => (string) $pendingCount, 'color' => 'amber', 'tab' => 'pending'],
            ['label' => 'Enrolled', 'value' => (string) $enrolledCount, 'color' => 'emerald', 'tab' => 'enrolled'],
            ['label' => 'Dropped', 'value' => (string) $droppedCount, 'color' => 'rose', 'tab' => 'dropped'],
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

        // Real-time analytics: students by academic level
        $levelStats = collect([
            'Senior High School',
            '1st Year College',
            '2nd Year College',
            '3rd Year College',
        ])->map(fn ($level) => [
            'label' => $level,
            'count' => User::where('role', 'student')->where('academic_level', $level)->count(),
        ])->all();

        // Real-time analytics: students by course
        $courseStats = collect(['BSIT', 'BSHM'])->map(fn ($c) => [
            'label' => $c,
            'count' => User::where('role', 'student')->where('course', $c)->count(),
        ])->all();

        return view('admin.enrollments', compact('enrollments', 'summary', 'tab', 'courseFilter', 'courseOptions', 'levelStats', 'courseStats'));
    }

    public function approveEnrollment(StudentModuleRecord $moduleRecord): RedirectResponse
    {
        $moduleRecord->update(['enrollment_status' => 'enrolled']);

        return redirect()
            ->route('admin.enrollments', ['tab' => 'pending'])
            ->with('status', 'Enrollment verified for '.$moduleRecord->user->name.' in '.$moduleRecord->module_name.'.');
    }

    public function assignSection(Request $request, StudentModuleRecord $moduleRecord): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string', 'max:50'],
        ]);

        $moduleRecord->update(['section' => $validated['section']]);

        return redirect()
            ->route('admin.enrollments', ['tab' => request()->query('tab', 'pending')])
            ->with('status', 'Section assigned: '.$validated['section'].' for '.$moduleRecord->user->name.'.');
    }

    public function encodeCourse(Request $request, StudentModuleRecord $moduleRecord): RedirectResponse
    {
        $validated = $request->validate([
            'module_name' => ['required', 'string', 'max:255'],
            'module_code' => ['required', 'string', 'max:20'],
            'instructor' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'string', 'max:255'],
        ]);

        $moduleRecord->update($validated);

        return redirect()
            ->route('admin.enrollments', ['tab' => 'enrolled'])
            ->with('status', 'Course details updated for '.$moduleRecord->user->name.'.');
    }

    public function auditTrail(Request $request): View
    {
        $userFilter = trim((string) $request->query('user', ''));
        $roleFilter = trim((string) $request->query('role', ''));
        $actionFilter = trim((string) $request->query('action', ''));
        $dateFilter = trim((string) $request->query('date', ''));

        $query = AuditTrail::with('user')
            ->when($userFilter !== '', function ($q) use ($userFilter) {
                $q->whereHas('user', function ($q2) use ($userFilter) {
                    $q2->where('name', 'like', "%{$userFilter}%");
                });
            })
            ->when($roleFilter !== '', function ($q) use ($roleFilter) {
                $q->whereHas('user', function ($q2) use ($roleFilter) {
                    $q2->where('role', $roleFilter);
                });
            })
            ->when($actionFilter !== '', function ($q) use ($actionFilter) {
                $q->where('action', $actionFilter);
            })
            ->when($dateFilter !== '', function ($q) use ($dateFilter) {
                $q->whereDate('timestamp', $dateFilter);
            })
            ->orderByDesc('timestamp')
            ->paginate(50)
            ->withQueryString();

        $actions = collect($query->items())->map(function (AuditTrail $at) {
            return [
                'time' => $at->timestamp ? Carbon::parse($at->timestamp)->format('M j, Y h:i A') : 'N/A',
                'user' => $at->user->name ?? 'System',
                'role' => $at->user->role ?? 'N/A',
                'action' => $at->action,
                'module' => $at->module,
                'ip' => $at->ip_address,
                'detail' => $at->detail,
            ];
        })->all();

        $stats = [
            ['label' => 'Total Actions', 'value' => AuditTrail::count()],
            ['label' => 'Logins',        'value' => AuditTrail::where('action', 'Login')->count()],
            ['label' => 'Creates',       'value' => AuditTrail::where('action', 'Create')->count()],
            ['label' => 'Updates',       'value' => AuditTrail::where('action', 'Update')->count()],
            ['label' => 'Deletes',       'value' => AuditTrail::where('action', 'Delete')->count()],
        ];

        return view('admin.audit-trail', [
            'actions' => $actions,
            'stats' => $stats,
            'userFilter' => $userFilter,
            'roleFilter' => $roleFilter,
            'actionFilter' => $actionFilter,
            'dateFilter' => $dateFilter,
            'pagination' => $query,
        ]);
    }

    public function systemMonitoring(): View
    {
        $serverStats = [
            ['label' => 'CPU Usage',    'value' => 0,  'unit' => '%',  'color' => 'emerald', 'status' => 'Normal'],
            ['label' => 'Memory Usage', 'value' => 0,  'unit' => '%',  'color' => 'amber',   'status' => 'Moderate'],
            ['label' => 'Disk Usage',   'value' => 0,  'unit' => '%',  'color' => 'sky',     'status' => 'Normal'],
            ['label' => 'Network I/O',  'value' => 0,  'unit' => 'MB/s', 'color' => 'violet', 'status' => 'Normal'],
        ];

        $platformStats = [
            ['label' => 'Total Users',          'value' => (string) User::count(),
                'icon' => 'users'],
            ['label' => 'Total Classrooms',      'value' => (string) Classroom::count(),
                'icon' => 'classroom'],
            ['label' => 'Attendance Records',    'value' => (string) FacultyAttendanceRecord::count(),
                'icon' => 'check'],
            ['label' => 'Document Requests',     'value' => (string) DocumentRequest::count(),
                'icon' => 'doc'],
            ['label' => 'Forum Posts',           'value' => (string) ForumThread::count(),
                'icon' => 'chat'],
        ];

        // Registration trend for the past 6 months
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $months->push($m->format('M'));
        }

        $registrations = User::selectRaw('MONTH(created_at) as month, role, COUNT(*) as cnt')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month', 'role')
            ->get()
            ->groupBy(['month', 'role']);

        $registrationTrend = $months->map(function ($label, $idx) use ($registrations) {
            $monthNum = Carbon::now()->subMonths(5 - $idx)->month;

            // Safely get the grouped results for the month (may be missing)
            $monthGroup = $registrations->get($monthNum, collect());

            // Each role group may also be missing; default to empty collection
            $students = $monthGroup->get('student', collect())->sum('cnt');
            $faculty = $monthGroup->get('faculty', collect())->sum('cnt');

            return ['month' => $label, 'students' => $students, 'faculty' => $faculty];
        })->all();

        $healthChecks = [
            ['name' => 'Database Connection',  'status' => DB::select("SELECT 1 AS ok") ? 'ok' : 'error', 'detail' => 'MySQL reachable'],
            ['name' => 'File Storage',         'status' => is_writable(storage_path('app')) ? 'ok' : 'error', 'detail' => 'Storage writable check'],
        ];

        return view('admin.system-monitoring', compact('serverStats', 'platformStats', 'registrationTrend', 'healthChecks'));
    }

    /**
     * Admin Profile page – shows editable profile fields and the admin's
     * own announcements (filtered by created_by).
     */
    public function profile(): View
    {
        $admin = auth()->user();

        // Announcements created by this admin only
        $myAnnouncements = Announcement::where('created_by', $admin->id)
            ->latest()
            ->get();

        return view('admin.profile', compact('admin', 'myAnnouncements'));
    }

    /**
     * Update individual or multiple admin profile fields.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = auth()->user();

        $validated = $request->validate([
            'title' => 'nullable|string|in:Dr.,Mr.,Ms.,Mrs.,Prof.,Engr.',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'office_hours' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:Male,Female,Other,Prefer not to say',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                Storage::disk('public')->delete($admin->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        } else {
            unset($validated['profile_photo']);
        }

        $admin->update($validated);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match our records.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        AuditTrail::log('Update', 'Settings', 'Admin updated their own password');

        return back()->with('status', 'Password updated successfully.');
    }

    public function facultyDirectory(Request $request): View
    {
        $search = $request->query('search', '');
        $statusFilter = $request->query('status', '');

        $query = User::where('role', 'faculty')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            });

        $facultyList = $query->paginate(15)->withQueryString();

        $totalFaculty = User::where('role', 'faculty')->count();
        $departments = User::where('role', 'faculty')->whereNotNull('department')->select('department')->distinct()->pluck('department');

        $departmentStats = [];
        foreach ($departments as $dept) {
            if ($dept) {
                $departmentStats[] = [
                    'label' => $dept,
                    'count' => User::where('role', 'faculty')->where('department', $dept)->count(),
                ];
            }
        }

        if (empty($departmentStats)) {
            // Fallback default levels if none are set
            $defaultLevels = ['Senior High School', '1st Year College', '2nd Year College', '3rd Year College'];
            foreach ($defaultLevels as $lvl) {
                $departmentStats[] = [
                    'label' => $lvl,
                    'count' => User::where('role', 'faculty')->where('department', $lvl)->count(),
                ];
            }
        }

        return view('admin.faculty.index', compact('facultyList', 'totalFaculty', 'departmentStats', 'search', 'statusFilter', 'departments'));
    }

    public function facultyShow(User $user): View
    {
        abort_if($user->role !== 'faculty', 404);

        // Load subjects they are teaching (classrooms)
        $classrooms = $user->classroomsAsFaculty()->withCount('students')->get();

        return view('admin.faculty.show', compact('user', 'classrooms'));
    }

    public function toggleFacultyStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role !== 'faculty', 404);

        $newStatus = $request->input('status') === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('status', "Faculty account for {$user->name} has been ".($newStatus === 'active' ? 'activated' : 'deactivated').'.');
    }
}
