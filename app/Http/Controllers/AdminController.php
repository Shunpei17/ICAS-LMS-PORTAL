<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\FacultyAttendanceRecord;
use App\Models\StudentModuleRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        // Fetch actual data from database
        $totalUsers = User::count();
        $activeTeachers = User::where('role', 'faculty')->count();
        $activeStudents = User::where('role', 'student')->count();
        $pendingRequests = User::where('role', 'pending')->count();

        $summary = [
            ['label' => 'Total Users', 'value' => (string) $totalUsers],
            ['label' => 'Active Teachers', 'value' => (string) $activeTeachers],
            ['label' => 'Active Students', 'value' => (string) $activeStudents],
            ['label' => 'Pending Requests', 'value' => (string) $pendingRequests],
        ];

        // Quick stats for enrollments, classrooms, attendance, announcements
        $totalEnrollments = StudentModuleRecord::distinct('user_id')->count();
        $totalClassrooms = StudentModuleRecord::distinct('module_name')->count();
        $totalAttendanceRecords = FacultyAttendanceRecord::count();
        $totalAnnouncements = Announcement::count();

        $overview = [
            ['title' => 'Total Enrollments', 'value' => (string) $totalEnrollments],
            ['title' => 'Active Classrooms', 'value' => (string) $totalClassrooms],
            ['title' => 'Attendance Records', 'value' => (string) $totalAttendanceRecords],
            ['title' => 'Total Announcements', 'value' => (string) $totalAnnouncements],
        ];

        // Recent actions - fetch from database
        $recentActions = [
            ['title' => 'Total Registered Users', 'subtitle' => $totalUsers.' users in the system'],
            ['title' => 'Active Courses', 'subtitle' => 'System is running smoothly'],
            ['title' => 'System Health', 'subtitle' => 'All systems operational'],
        ];

        return view('admin.dashboard', compact('summary', 'overview', 'recentActions'));
    }

    public function attendance(Request $request): View
    {
        $filters = $this->resolveAttendanceFilters($request);
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
        $uniqueStudents = (clone $baseQuery)
            ->select('student_name')
            ->distinct()
            ->count('student_name');

        $attendanceRate = $totalRecords > 0
            ? (string) round(($presentRecords / $totalRecords) * 100).'%'
            : '0%';

        $summary = [
            ['label' => 'Total Records', 'value' => (string) $totalRecords],
            ['label' => 'Present', 'value' => (string) $presentRecords],
            ['label' => 'Absent', 'value' => (string) $absentRecords],
            ['label' => 'Late', 'value' => (string) $lateRecords],
            ['label' => 'Attendance Rate', 'value' => $attendanceRate],
            ['label' => 'Unique Students', 'value' => (string) $uniqueStudents],
        ];

        $records = (clone $baseQuery)
            ->with(['faculty:id,name'])
            ->orderByDesc('attendance_date')
            ->orderBy('student_name')
            ->paginate(12)
            ->withQueryString();

        $classOptions = FacultyAttendanceRecord::query()
            ->select('student_class')
            ->distinct()
            ->orderBy('student_class')
            ->pluck('student_class')
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

        return view('admin.attendance', compact('summary', 'records', 'filters', 'activeFilters', 'classOptions', 'facultyOptions'));
    }

    public function grades(): View
    {
        $grades = StudentModuleRecord::query()
            ->whereNotNull('grade_percent')
            ->selectRaw('module_name, AVG(grade_percent) as average_grade')
            ->groupBy('module_name')
            ->orderBy('module_name')
            ->get()
            ->map(function (StudentModuleRecord $record): array {
                $averageGrade = (float) ($record->average_grade ?? 0);

                return [
                    'course' => $record->module_name,
                    'average' => number_format($averageGrade, 0).'%',
                    'status' => $this->resolveGradeStatus($averageGrade),
                ];
            })
            ->all();

        return view('admin.grades', compact('grades'));
    }

    public function exportGrades(): StreamedResponse
    {
        $records = StudentModuleRecord::query()
            ->with(['user:id,name,email'])
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
            fputcsv($output, ['Student Name', 'Student Email', 'Module Name', 'Module Code', 'Instructor', 'Schedule', 'Grade Percent']);

            foreach ($records as $record) {
                fputcsv($output, [
                    $record->user?->name ?? 'Unknown Student',
                    $record->user?->email ?? '',
                    $record->module_name,
                    $record->module_code,
                    $record->instructor ?? '',
                    $record->schedule ?? '',
                    $record->grade_percent !== null ? number_format((float) $record->grade_percent, 2) : '',
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function classrooms(): View
    {
        $classrooms = [
            ['name' => 'Advanced Mathematics', 'teacher' => 'Dr. Maria Fernandez', 'students' => 28, 'status' => 'Active'],
            ['name' => 'Physics I', 'teacher' => 'Mr. Paulo Navarro', 'students' => 24, 'status' => 'Active'],
            ['name' => 'World History', 'teacher' => 'Mrs. Grace Bautista', 'students' => 30, 'status' => 'Active'],
        ];

        return view('admin.classrooms', compact('classrooms'));
    }

    public function documents(): View
    {
        $documents = [
            ['title' => 'Transcript Request', 'status' => 'Approved', 'requested' => '3/25/2026'],
            ['title' => 'Enrollment Certificate', 'status' => 'Pending', 'requested' => '3/28/2026'],
            ['title' => 'Policy Manual', 'status' => 'Published', 'requested' => '3/29/2026'],
        ];

        return view('admin.documents', compact('documents'));
    }

    public function forum(): View
    {
        $threads = [
            ['title' => 'Staff meeting agenda', 'activity' => '6 comments'],
            ['title' => 'System update schedule', 'activity' => '3 comments'],
        ];

        return view('admin.forum', compact('threads'));
    }

    /**
     * @return array{search: string, status: string, student_class: string, faculty_user_id: string, from_date: string, to_date: string}
     */
    private function resolveAttendanceFilters(Request $request): array
    {
        $status = trim((string) $request->query('status', ''));

        if (! in_array($status, ['Present', 'Absent', 'Late'], true)) {
            $status = '';
        }

        $facultyUserId = trim((string) $request->query('faculty_user_id', ''));

        if ($facultyUserId !== '' && ! ctype_digit($facultyUserId)) {
            $facultyUserId = '';
        }

        return [
            'search' => trim((string) $request->query('search', '')),
            'status' => $status,
            'student_class' => trim((string) $request->query('student_class', '')),
            'faculty_user_id' => $facultyUserId,
            'from_date' => trim((string) $request->query('from_date', '')),
            'to_date' => trim((string) $request->query('to_date', '')),
        ];
    }

    /**
     * @param  array{search: string, status: string, student_class: string, faculty_user_id: string, from_date: string, to_date: string}  $filters
     */
    private function queryAttendanceRecords(array $filters): Builder
    {
        return FacultyAttendanceRecord::query()
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->where('student_name', 'like', '%'.$filters['search'].'%');
            })
            ->when($filters['status'] !== '', function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when($filters['student_class'] !== '', function (Builder $query) use ($filters): void {
                $query->where('student_class', $filters['student_class']);
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
}
