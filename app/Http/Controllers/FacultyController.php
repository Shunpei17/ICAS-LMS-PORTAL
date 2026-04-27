<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacultyAttendanceRecordRequest;
use App\Http\Requests\UpdateFacultyAttendanceRecordRequest;
use App\Models\FacultyAttendanceRecord;
use App\Models\StudentModuleRecord;
use Illuminate\Database\Eloquent\Builder;
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
                'faculty_id'  => 'FAC-' . str_pad($user->id ?? 1, 4, '0', STR_PAD_LEFT),
                'name'        => $user->name ?? 'Dr. Maria Santos',
                'email'       => $user->email ?? 'faculty@icas.edu',
                'phone'       => '+63 917 654 3210',
                'department'  => 'College of Engineering & Technology',
                'designation' => 'Associate Professor',
                'office'      => 'Faculty Office, 3rd Floor CET Building',
                'office_hours'=> 'Mon, Wed, Fri — 10:00 AM to 12:00 PM',
                'subjects'    => ['Advanced Mathematics (MATH301)', 'Physics I (PHY201)', 'World History (HIST201)', 'English Composition (ENG101)'],
                'status'      => 'Active',
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

    public function students(): View
    {
        $subjects = [
            [
                'slug'        => 'math301',
                'code'        => 'MATH301',
                'name'        => 'Advanced Mathematics',
                'units'       => 3,
                'schedule'    => 'Mon, Wed, Fri — 9:00 AM',
                'description' => 'Covers calculus, linear algebra, and differential equations.',
                'enrolled'    => 28,
                'color'       => 'emerald',
            ],
            [
                'slug'        => 'phy201',
                'code'        => 'PHY201',
                'name'        => 'Physics I',
                'units'       => 5,
                'schedule'    => 'Tue, Thu — 10:00 AM',
                'description' => 'Mechanics, kinematics, thermodynamics, and wave motion.',
                'enrolled'    => 24,
                'color'       => 'sky',
            ],
            [
                'slug'        => 'hist201',
                'code'        => 'HIST201',
                'name'        => 'World History',
                'units'       => 3,
                'schedule'    => 'Mon, Wed — 2:00 PM',
                'description' => 'Survey of world civilizations from antiquity to the modern era.',
                'enrolled'    => 30,
                'color'       => 'amber',
            ],
            [
                'slug'        => 'eng101',
                'code'        => 'ENG101',
                'name'        => 'English Composition',
                'units'       => 3,
                'schedule'    => 'Fri — 1:00 PM',
                'description' => 'Academic writing, critical thinking, and research skills.',
                'enrolled'    => 22,
                'color'       => 'violet',
            ],
        ];

        return view('faculty.students', compact('subjects'));
    }

    public function subjectShow(string $slug): View
    {
        $allSubjects = [
            'math301' => [
                'slug' => 'math301', 'code' => 'MATH301', 'name' => 'Advanced Mathematics',
                'units' => 3, 'schedule' => 'Mon, Wed, Fri — 9:00 AM',
                'description' => 'Covers calculus, linear algebra, and differential equations.',
                'enrolled' => 28, 'color' => 'emerald',
                'topics' => [
                    [
                        'title' => 'Unit 1: Limits and Continuity',
                        'posts' => [
                            ['type' => 'material', 'title' => 'Course Syllabus',              'body' => 'Full syllabus for MATH301. Please read before our first class.',         'date' => 'Sep 1',  'icon' => 'doc'],
                            ['type' => 'material', 'title' => 'Lecture Notes — Limits',       'body' => 'Complete notes from the first lecture covering limit definitions.',        'date' => 'Sep 3',  'icon' => 'doc'],
                            ['type' => 'assignment','title' => 'Problem Set 1',               'body' => 'Solve exercises 1–20 from Chapter 2. Due next Monday.',                  'date' => 'Sep 5',  'icon' => 'assign'],
                        ],
                    ],
                    [
                        'title' => 'Unit 2: Derivatives',
                        'posts' => [
                            ['type' => 'material', 'title' => 'Lecture Slides — Derivatives', 'body' => 'Slides covering rules of differentiation (product, chain, quotient).',   'date' => 'Sep 10', 'icon' => 'doc'],
                            ['type' => 'material', 'title' => 'Video: Chain Rule Explained',  'body' => 'Watch this 12-minute video before the next class session.',               'date' => 'Sep 12', 'icon' => 'video'],
                            ['type' => 'assignment','title' => 'Problem Set 2',               'body' => 'Exercises 1–30 from Chapter 3. Show complete solutions.',                'date' => 'Sep 14', 'icon' => 'assign'],
                            ['type' => 'quiz',     'title' => 'Quiz 1 — Derivatives',         'body' => 'Online quiz covering Units 1 & 2. 30 minutes. Closes Sep 18 11:59 PM.', 'date' => 'Sep 16', 'icon' => 'quiz'],
                        ],
                    ],
                    [
                        'title' => 'Unit 3: Integration',
                        'posts' => [
                            ['type' => 'material', 'title' => 'Lecture Notes — Integration',  'body' => 'Antiderivatives, Riemann sums, and the Fundamental Theorem of Calculus.','date' => 'Sep 22', 'icon' => 'doc'],
                            ['type' => 'material', 'title' => 'Integration Formula Sheet',    'body' => 'Printable formula reference for common integrals.',                       'date' => 'Sep 24', 'icon' => 'doc'],
                            ['type' => 'assignment','title' => 'Problem Set 3',               'body' => 'Integration exercises. Due Oct 1.',                                       'date' => 'Sep 26', 'icon' => 'assign'],
                        ],
                    ],
                ],
            ],
            'phy201' => [
                'slug' => 'phy201', 'code' => 'PHY201', 'name' => 'Physics I',
                'units' => 5, 'schedule' => 'Tue, Thu — 10:00 AM',
                'description' => 'Mechanics, kinematics, thermodynamics, and wave motion.',
                'enrolled' => 24, 'color' => 'sky',
                'topics' => [
                    [
                        'title' => 'Unit 1: Kinematics',
                        'posts' => [
                            ['type' => 'material',  'title' => 'Physics I Syllabus',           'body' => 'Course requirements and grading policy.',                                'date' => 'Sep 1',  'icon' => 'doc'],
                            ['type' => 'material',  'title' => 'Lecture: Motion in 1D',        'body' => 'Displacement, velocity, acceleration — definitions and equations.',      'date' => 'Sep 3',  'icon' => 'doc'],
                            ['type' => 'material',  'title' => 'Video: Projectile Motion',     'body' => 'Pre-class viewing required. ~15 minutes.',                               'date' => 'Sep 5',  'icon' => 'video'],
                            ['type' => 'assignment','title' => 'Lab Report 1',                 'body' => 'Free-fall experiment lab report. Follow the template provided.',         'date' => 'Sep 8',  'icon' => 'assign'],
                        ],
                    ],
                    [
                        'title' => 'Unit 2: Newton\'s Laws',
                        'posts' => [
                            ['type' => 'material',  'title' => 'Lecture Slides — Forces',     'body' => 'Free body diagrams, net force, and Newton\'s 3 laws.',                  'date' => 'Sep 15', 'icon' => 'doc'],
                            ['type' => 'quiz',      'title' => 'Quiz 1 — Kinematics',         'body' => 'Covers Unit 1 entirely. 25 minutes. Timed.',                             'date' => 'Sep 17', 'icon' => 'quiz'],
                            ['type' => 'assignment','title' => 'Problem Set: Forces',         'body' => 'Newton\'s law problems. Show all working.',                              'date' => 'Sep 20', 'icon' => 'assign'],
                        ],
                    ],
                ],
            ],
            'hist201' => [
                'slug' => 'hist201', 'code' => 'HIST201', 'name' => 'World History',
                'units' => 3, 'schedule' => 'Mon, Wed — 2:00 PM',
                'description' => 'Survey of world civilizations from antiquity to the modern era.',
                'enrolled' => 30, 'color' => 'amber',
                'topics' => [
                    [
                        'title' => 'Unit 1: Ancient Civilizations',
                        'posts' => [
                            ['type' => 'material', 'title' => 'Course Overview & Syllabus',   'body' => 'Course structure, reading list, and assessment details.',                 'date' => 'Sep 1',  'icon' => 'doc'],
                            ['type' => 'material', 'title' => 'Reading: Mesopotamia',         'body' => 'Chapter 1 of the textbook — Sumer, Babylon, and Akkad.',                'date' => 'Sep 3',  'icon' => 'doc'],
                            ['type' => 'assignment','title' => 'Essay: Rise of Civilization', 'body' => '500-word essay on the key factors that led to early civilizations.',     'date' => 'Sep 10', 'icon' => 'assign'],
                        ],
                    ],
                    [
                        'title' => 'Unit 2: Classical Empires',
                        'posts' => [
                            ['type' => 'material', 'title' => 'Lecture: The Roman Empire',    'body' => 'Rise, expansion, and fall of Rome. Includes primary sources.',           'date' => 'Sep 17', 'icon' => 'doc'],
                            ['type' => 'quiz',     'title' => 'Quiz: Ancient World',          'body' => 'Covers Units 1 and early Unit 2. 20 questions.',                         'date' => 'Sep 22', 'icon' => 'quiz'],
                        ],
                    ],
                ],
            ],
            'eng101' => [
                'slug' => 'eng101', 'code' => 'ENG101', 'name' => 'English Composition',
                'units' => 3, 'schedule' => 'Fri — 1:00 PM',
                'description' => 'Academic writing, critical thinking, and research skills.',
                'enrolled' => 22, 'color' => 'violet',
                'topics' => [
                    [
                        'title' => 'Unit 1: Academic Writing Basics',
                        'posts' => [
                            ['type' => 'material',  'title' => 'Syllabus & Writing Guide',    'body' => 'Course outline and the ICAS Academic Writing Style Guide.',               'date' => 'Sep 1',  'icon' => 'doc'],
                            ['type' => 'material',  'title' => 'Paragraph Structure',         'body' => 'Slides: Topic sentence, supporting details, and concluding sentence.',   'date' => 'Sep 5',  'icon' => 'doc'],
                            ['type' => 'assignment','title' => 'Draft 1: Descriptive Essay',  'body' => '3-paragraph descriptive essay. Submit via portal.',                      'date' => 'Sep 12', 'icon' => 'assign'],
                        ],
                    ],
                    [
                        'title' => 'Unit 2: Research & Citation',
                        'posts' => [
                            ['type' => 'material',  'title' => 'APA Citation Guide',          'body' => 'How to cite books, websites, and journals in APA 7th edition.',          'date' => 'Sep 19', 'icon' => 'doc'],
                            ['type' => 'assignment','title' => 'Annotated Bibliography',      'body' => 'Annotate 5 sources on a topic of your choice. Due Sep 30.',              'date' => 'Sep 22', 'icon' => 'assign'],
                            ['type' => 'quiz',      'title' => 'Quiz: Citation Formats',      'body' => '15 questions on APA formatting. Open-notes. 30 minutes.',                'date' => 'Sep 26', 'icon' => 'quiz'],
                        ],
                    ],
                ],
            ],
        ];

        $subject = $allSubjects[$slug] ?? null;

        abort_if($subject === null, 404);

        $enrolledStudents = [
            ['id' => 1, 'initials' => 'MS', 'name' => 'Miguel Santos', 'email' => 'miguel.s@school.edu', 'grade' => '92%', 'status' => 'Excellent'],
            ['id' => 2, 'initials' => 'AR', 'name' => 'Andrea Reyes', 'email' => 'andrea.r@school.edu', 'grade' => '88%', 'status' => 'Good'],
            ['id' => 3, 'initials' => 'CD', 'name' => 'Carlo Dela Cruz', 'email' => 'carlo.c@school.edu', 'grade' => '75%', 'status' => 'Needs Improvement'],
        ];

        return view('faculty.subject-show', compact('subject', 'enrolledStudents'));
    }

    public function studentShow(string $id): View
    {
        // Placeholder student details for faculty dashboard
        $student = [
            'id' => $id,
            'student_id' => 'STU-' . str_pad($id, 4, '0', STR_PAD_LEFT),
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

        $gradeSubjectFilter = $request->query('grade_subject', '');
        $gradeSearch = trim((string) $request->query('grade_search', ''));

        $studentsWithGrades = collect();
        if ($tab === 'grades') {
            // Get all students enrolled in the faculty's modules, or just all students for simplicity
            $studentsQuery = \App\Models\User::where('role', 'student');
            if ($gradeSearch) {
                $studentsQuery->where('name', 'like', '%' . $gradeSearch . '%');
            }
            $students = $studentsQuery->get();

            $gradeQuery = \App\Models\Grade::query();
            if ($gradeSubjectFilter) {
                $gradeQuery->where('subject_id', $gradeSubjectFilter);
            }
            $existingGrades = $gradeQuery->get()->keyBy('student_id');

            foreach ($students as $student) {
                $studentsWithGrades->push([
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'subject_id' => $gradeSubjectFilter ?: 'MATH301', // Default subject
                    'quiz' => $existingGrades->has($student->id) ? $existingGrades[$student->id]->quiz : null,
                    'assignment' => $existingGrades->has($student->id) ? $existingGrades[$student->id]->assignment : null,
                    'exam' => $existingGrades->has($student->id) ? $existingGrades[$student->id]->exam : null,
                    'average' => $existingGrades->has($student->id) ? $existingGrades[$student->id]->average : null,
                    'remarks' => $existingGrades->has($student->id) ? $existingGrades[$student->id]->remarks : null,
                ]);
            }
        }

        $gradeSubjects = ['MATH301', 'PHY201', 'HIST201', 'ENG101'];

        return view('faculty.grades', compact('summary', 'records', 'filters', 'activeFilters', 'classOptions', 'tab', 'studentsWithGrades', 'gradeSubjects', 'gradeSubjectFilter', 'gradeSearch'));
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

        $enrollments = StudentModuleRecord::query()
            ->when($tab === 'enrolled', fn($q) => $q->whereIn('enrollment_status', ['faculty_approved', 'enrolled']))
            ->when($tab !== 'enrolled', fn($q) => $q->where('enrollment_status', $tab))
            ->with(['user:id,name,email'])
            ->when($courseFilter !== '', function ($query) use ($courseFilter): void {
                $query->where('module_code', $courseFilter);
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $enrolledCount = StudentModuleRecord::whereIn('enrollment_status', ['faculty_approved', 'enrolled'])->count();
        $pendingCount  = StudentModuleRecord::where('enrollment_status', 'pending')->count();
        $droppedCount  = StudentModuleRecord::where('enrollment_status', 'dropped')->count();

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
}

