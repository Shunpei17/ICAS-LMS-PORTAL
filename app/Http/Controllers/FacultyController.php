<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacultyAttendanceRecordRequest;
use App\Http\Requests\UpdateFacultyAttendanceRecordRequest;
use App\Models\FacultyAttendanceRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacultyController extends Controller
{
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

    public function students(): View
    {
        $students = [
            ['initials' => 'MS', 'name' => 'Miguel Santos', 'email' => 'miguel.s@school.edu', 'grade' => '10th', 'class' => 'A', 'enrolled' => '9/1/2024', 'status' => 'active'],
            ['initials' => 'AR', 'name' => 'Andrea Reyes', 'email' => 'andrea.r@school.edu', 'grade' => '10th', 'class' => 'A', 'enrolled' => '9/1/2024', 'status' => 'active'],
            ['initials' => 'CD', 'name' => 'Carlo Dela Cruz', 'email' => 'carlo.c@school.edu', 'grade' => '10th', 'class' => 'B', 'enrolled' => '9/1/2024', 'status' => 'active'],
            ['initials' => 'BV', 'name' => 'Bea Villanueva', 'email' => 'bea.v@school.edu', 'grade' => '11th', 'class' => 'A', 'enrolled' => '9/1/2023', 'status' => 'active'],
            ['initials' => 'JM', 'name' => 'Janelle Mendoza', 'email' => 'janelle.m@school.edu', 'grade' => '11th', 'class' => 'B', 'enrolled' => '9/1/2023', 'status' => 'active'],
            ['initials' => 'PD', 'name' => 'Paolo Domingo', 'email' => 'paolo.d@school.edu', 'grade' => '9th', 'class' => 'A', 'enrolled' => '9/1/2025', 'status' => 'active'],
        ];

        return view('faculty.students', compact('students'));
    }

    public function grades(Request $request): View
    {
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

        return view('faculty.grades', compact('summary', 'records', 'filters', 'activeFilters', 'classOptions'));
    }

    public function storeAttendanceRecord(StoreFacultyAttendanceRecordRequest $request): RedirectResponse
    {
        FacultyAttendanceRecord::query()->create([
            'faculty_user_id' => Auth::id(),
            ...$request->validated(),
        ]);

        return redirect()
            ->route('faculty.grades')
            ->with('status', 'Attendance record registered successfully.');
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

            fputcsv($output, ['Student Name', 'Class', 'Date', 'Status']);

            foreach ($records as $record) {
                fputcsv($output, [
                    $record->student_name,
                    $record->student_class,
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
            'from_date',
            'to_date',
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
     * @return array{search: string, status: string, student_class: string, from_date: string, to_date: string}
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
            'from_date' => trim((string) $request->query('from_date', '')),
            'to_date' => trim((string) $request->query('to_date', '')),
        ];
    }

    /**
     * @param  array{search: string, status: string, student_class: string, from_date: string, to_date: string}  $filters
     */
    private function queryAttendanceRecords(array $filters): Builder
    {
        return FacultyAttendanceRecord::query()
            ->where('faculty_user_id', Auth::id())
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->where('student_name', 'like', '%'.$filters['search'].'%');
            })
            ->when($filters['status'] !== '', function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when($filters['student_class'] !== '', function (Builder $query) use ($filters): void {
                $query->where('student_class', $filters['student_class']);
            })
            ->when($filters['from_date'] !== '', function (Builder $query) use ($filters): void {
                $query->whereDate('attendance_date', '>=', $filters['from_date']);
            })
            ->when($filters['to_date'] !== '', function (Builder $query) use ($filters): void {
                $query->whereDate('attendance_date', '<=', $filters['to_date']);
            });
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
}
