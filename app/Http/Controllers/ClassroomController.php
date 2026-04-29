<?php

namespace App\Http\Controllers;

use App\Exports\ClassroomStudentsExport;
use App\Models\Classroom;
use App\Models\FacultyAttendanceRecord;
use App\Models\StudentModuleRecord;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ClassroomController extends Controller
{
    // ─────────────────────────────────────────────
    // FACULTY
    // ─────────────────────────────────────────────

    public function facultyIndex(): View
    {
        /** @var User $faculty */
        $faculty = Auth::user();

        $classrooms = $faculty->classroomsAsFaculty()
            ->withCount('students')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Classroom $c): array {
                $avgGrade = StudentModuleRecord::where('module_code', $c->code)
                    ->whereNotNull('grade_percent')
                    ->avg('grade_percent');

                $totalAttendance = FacultyAttendanceRecord::where('faculty_user_id', $c->faculty_user_id)
                    ->where('student_class', $c->code)
                    ->count();

                $presentAttendance = FacultyAttendanceRecord::where('faculty_user_id', $c->faculty_user_id)
                    ->where('student_class', $c->code)
                    ->where('status', 'Present')
                    ->count();

                $attendanceRate = $totalAttendance > 0
                    ? round(($presentAttendance / $totalAttendance) * 100)
                    : null;

                return [
                    'id'             => $c->id,
                    'name'           => $c->name,
                    'code'           => $c->code,
                    'schedule'       => $c->schedule,
                    'description'    => $c->description,
                    'status'         => $c->status,
                    'student_count'  => $c->students_count,
                    'avg_grade'      => $avgGrade !== null ? number_format((float) $avgGrade, 0).'%' : null,
                    'attendance_rate' => $attendanceRate !== null ? $attendanceRate.'%' : null,
                    'created_at'     => $c->created_at?->format('M j, Y'),
                ];
            })
            ->all();

        return view('faculty.classrooms', compact('classrooms'));
    }

    public function facultyCreate(): View
    {
        $classroom = null;

        return view('faculty.classroom-form', compact('classroom'));
    }

    public function facultyStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:20', 'unique:classrooms,code'],
            'schedule'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', 'in:active,inactive'],
        ]);

        /** @var User $faculty */
        $faculty = Auth::user();

        $faculty->classroomsAsFaculty()->create($validated);

        return redirect()
            ->route('faculty.classrooms')
            ->with('status', 'Classroom "'.$validated['name'].'" created successfully.');
    }

    public function facultyEdit(Classroom $classroom): View
    {
        $this->authorizeClassroom($classroom);

        return view('faculty.classroom-form', compact('classroom'));
    }

    public function facultyUpdate(Request $request, Classroom $classroom): RedirectResponse
    {
        $this->authorizeClassroom($classroom);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:20', 'unique:classrooms,code,'.$classroom->id],
            'schedule'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', 'in:active,inactive'],
        ]);

        $classroom->update($validated);

        return redirect()
            ->route('faculty.classrooms')
            ->with('status', 'Classroom "'.$classroom->name.'" updated successfully.');
    }

    public function facultyShow(Classroom $classroom): View
    {
        $this->authorizeClassroom($classroom);

        $classroom->load('students');

        // Students enrolled in this classroom
        $students = $classroom->students->map(function (User $student) use ($classroom): array {
            $moduleRecord = StudentModuleRecord::where('user_id', $student->id)
                ->where('module_code', $classroom->code)
                ->first();

            $attendanceTotal = FacultyAttendanceRecord::where('faculty_user_id', $classroom->faculty_user_id)
                ->where('student_class', $classroom->code)
                ->where('student_name', 'like', '%'.explode(' ', $student->name)[0].'%')
                ->count();

            $attendancePresent = FacultyAttendanceRecord::where('faculty_user_id', $classroom->faculty_user_id)
                ->where('student_class', $classroom->code)
                ->where('student_name', 'like', '%'.explode(' ', $student->name)[0].'%')
                ->where('status', 'Present')
                ->count();

            return [
                'id'              => $student->id,
                'name'            => $student->name,
                'email'           => $student->email,
                'initials'        => $this->extractInitials($student->name),
                'grade'           => $moduleRecord?->grade_percent !== null
                                        ? number_format((float) $moduleRecord->grade_percent, 0).'%'
                                        : null,
                'section'         => $moduleRecord?->section,
                'enrollment_status' => $moduleRecord?->enrollment_status ?? 'pending',
                'attendance_rate'  => $attendanceTotal > 0
                                        ? round(($attendancePresent / $attendanceTotal) * 100).'%'
                                        : null,
                'enrolled_at'     => $student->pivot->enrolled_at
                                        ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('M j, Y')
                                        : null,
            ];
        })->all();

        // Attendance records for this classroom
        $attendanceRecords = FacultyAttendanceRecord::where('faculty_user_id', $classroom->faculty_user_id)
            ->where('student_class', $classroom->code)
            ->orderByDesc('attendance_date')
            ->get()
            ->map(function (FacultyAttendanceRecord $r): array {
                return [
                    'student_name' => $r->student_name,
                    'date'         => $r->attendance_date->format('M j, Y'),
                    'status'       => $r->status,
                ];
            })
            ->all();

        // Grade summary per student
        $gradeRecords = StudentModuleRecord::where('module_code', $classroom->code)
            ->with('user:id,name')
            ->whereNotNull('grade_percent')
            ->get()
            ->map(function (StudentModuleRecord $r): array {
                return [
                    'name'  => $r->user?->name ?? 'Unknown',
                    'grade' => number_format((float) $r->grade_percent, 0).'%',
                    'value' => (float) $r->grade_percent,
                ];
            })
            ->all();

        $avgGrade = count($gradeRecords) > 0
            ? number_format(collect($gradeRecords)->avg('value'), 0).'%'
            : null;

        return view('faculty.classroom-show', compact('classroom', 'students', 'attendanceRecords', 'gradeRecords', 'avgGrade'));
    }

    // ─────────────────────────────────────────────
    // STUDENT
    // ─────────────────────────────────────────────

    public function studentIndex(): View
    {
        /** @var User $student */
        $student = Auth::user();

        $enrolledIds = $student->classroomsAsStudent()->pluck('classrooms.id')->all();

        $classrooms = Classroom::where('status', 'active')
            ->with('faculty:id,name')
            ->withCount('students')
            ->orderBy('name')
            ->get()
            ->map(function (Classroom $c) use ($enrolledIds, $student): array {
                $isEnrolled = in_array($c->id, $enrolledIds, true);

                $moduleRecord = $isEnrolled
                    ? StudentModuleRecord::where('user_id', $student->id)
                        ->where('module_code', $c->code)
                        ->first()
                    : null;

                return [
                    'id'               => $c->id,
                    'name'             => $c->name,
                    'code'             => $c->code,
                    'schedule'         => $c->schedule,
                    'description'      => $c->description,
                    'faculty_name'     => $c->faculty?->name ?? 'Instructor TBA',
                    'student_count'    => $c->students_count,
                    'is_enrolled'      => $isEnrolled,
                    'grade'            => $moduleRecord?->grade_percent !== null
                                             ? number_format((float) $moduleRecord->grade_percent, 0).'%'
                                             : null,
                    'enrollment_status' => $moduleRecord?->enrollment_status,
                    'section'          => $moduleRecord?->section,
                ];
            })
            ->all();

        $myClassrooms  = array_filter($classrooms, fn (array $c): bool => $c['is_enrolled']);
        $openClassrooms = array_filter($classrooms, fn (array $c): bool => !$c['is_enrolled']);

        return view('student.classrooms', compact('myClassrooms', 'openClassrooms'));
    }

    public function studentEnroll(Request $request, Classroom $classroom): RedirectResponse
    {
        if ($classroom->status !== 'active') {
            return redirect()->route('student.classrooms')
                ->withErrors(['classroom' => 'This classroom is not accepting new students.']);
        }

        /** @var User $student */
        $student = Auth::user();

        // Prevent duplicate
        if ($student->classroomsAsStudent()->where('classrooms.id', $classroom->id)->exists()) {
            return redirect()->route('student.classrooms')
                ->withErrors(['classroom' => 'You are already enrolled in '.$classroom->name.'.']);
        }

        $student->classroomsAsStudent()->attach($classroom->id, ['enrolled_at' => now()]);

        return redirect()
            ->route('student.classrooms')
            ->with('status', 'You have successfully joined "'.$classroom->name.'"!');
    }

    // ─────────────────────────────────────────────
    // ADMIN
    // ─────────────────────────────────────────────

    public function adminIndex(Request $request): View
    {
        $statusFilter = in_array($request->query('status'), ['active', 'inactive'], true)
            ? $request->query('status')
            : '';

        $search = trim((string) $request->query('search', ''));

        $classrooms = Classroom::query()
            ->with('faculty:id,name')
            ->withCount('students')
            ->when($statusFilter !== '', fn ($q) => $q->where('status', $statusFilter))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('code', 'like', '%'.$search.'%');
            }))
            ->orderBy('name')
            ->get()
            ->map(function (Classroom $c): array {
                $avgGrade = StudentModuleRecord::where('module_code', $c->code)
                    ->whereNotNull('grade_percent')
                    ->avg('grade_percent');

                $totalAttendance = FacultyAttendanceRecord::where('student_class', $c->code)->count();
                $presentAttendance = FacultyAttendanceRecord::where('student_class', $c->code)
                    ->where('status', 'Present')
                    ->count();

                return [
                    'id'              => $c->id,
                    'name'            => $c->name,
                    'code'            => $c->code,
                    'schedule'        => $c->schedule,
                    'faculty_name'    => $c->faculty?->name ?? 'Unassigned',
                    'student_count'   => $c->students_count,
                    'status'          => $c->status,
                    'avg_grade'       => $avgGrade !== null ? number_format((float) $avgGrade, 0).'%' : '—',
                    'attendance_rate' => $totalAttendance > 0
                                            ? round(($presentAttendance / $totalAttendance) * 100).'%'
                                            : '—',
                ];
            })
            ->all();

        $totalClassrooms    = Classroom::count();
        $activeClassrooms   = Classroom::where('status', 'active')->count();
        $totalStudentsEnrolled = \DB::table('classroom_students')->distinct('user_id')->count('user_id');

        $summary = [
            ['label' => 'Total Classrooms',    'value' => (string) $totalClassrooms,      'color' => 'slate'],
            ['label' => 'Active Classrooms',   'value' => (string) $activeClassrooms,     'color' => 'emerald'],
            ['label' => 'Students Enrolled',   'value' => (string) $totalStudentsEnrolled,'color' => 'sky'],
        ];

        return view('admin.classrooms', compact('classrooms', 'summary', 'statusFilter', 'search'));
    }

    /**
     * Show classroom detail for admin (student list, search, export).
     */
    public function adminShow(Request $request, Classroom $classroom): View
    {
        $search = trim((string) $request->query('q', ''));

        $query = $classroom->students()->select('users.*');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', '%'.$search.'%')
                  ->orWhere('name', 'like', '%'.$search.'%')
                  ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $total = $query->count();
        $students = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.classroom-show', compact('classroom', 'students', 'total', 'search'));
    }

    /**
     * Toggle classroom status (active/inactive) for admin.
     */
    public function adminToggleStatus(Request $request, Classroom $classroom)
    {
        $classroom->status = $classroom->status === 'active' ? 'inactive' : 'active';
        $classroom->save();

        return response()->json(['status' => $classroom->status]);
    }

    /**
     * Assign faculty to a classroom (admin action).
     */
    public function adminAssignFaculty(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'faculty_user_id' => ['required', 'exists:users,id'],
        ]);

        $classroom->faculty_user_id = $validated['faculty_user_id'];
        $classroom->save();

        return redirect()->route('admin.classrooms')->with('status', 'Faculty assigned.');
    }

    /**
     * Export classroom student list. Supports `csv`. For full Excel/PDF support,
     * install Laravel-Excel or DomPDF and adapt this method.
     */
    public function adminExport(Request $request, Classroom $classroom)
    {
        if (! auth()->user() || ! auth()->user()->can('manage', $classroom)) {
            abort(403);
        }
        $format = $request->query('format', 'csv');
        $students = $classroom->students()->orderBy('name')->get();

        if (in_array($format, ['xlsx', 'xls'], true)) {
            $filename = 'classroom-'.$classroom->code.'-students-'.date('Ymd').'.xlsx';
            return Excel::download(new ClassroomStudentsExport($students), $filename);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.exports.classroom-students', compact('classroom', 'students'));
            return $pdf->download('classroom-'.$classroom->code.'-students-'.date('Ymd').'.pdf');
        }

        // Default to CSV
        $filename = 'classroom-'.$classroom->code.'-students-'.date('Ymd').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student Number', 'Full Name', 'Academic Level', 'Enrollment Status', 'Email', 'Enrolled At']);

            foreach ($students as $s) {
                fputcsv($handle, [
                    $s->student_number ?? '',
                    $s->name ?? '',
                    $s->academic_level ?? '',
                    $s->pivot->enrollment_status ?? '',
                    $s->email ?? '',
                    $s->pivot->enrolled_at ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    private function authorizeClassroom(Classroom $classroom): void
    {
        if ((int) $classroom->faculty_user_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    private function extractInitials(string $name): string
    {
        $segments = preg_split('/\s+/', trim($name)) ?: [];
        $initials = collect($segments)
            ->filter()
            ->take(2)
            ->map(fn (string $s): string => strtoupper(substr($s, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'NA';
    }
}
