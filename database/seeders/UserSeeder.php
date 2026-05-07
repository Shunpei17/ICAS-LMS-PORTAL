<?php

namespace Database\Seeders;

use App\Models\FacultyAttendanceRecord;
use App\Models\StudentModuleRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@school.edu'],
            [
                'name' => 'Ramon Dela Cruz',
                'password' => 'password123',
                'role' => 'admin',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'faculty@school.edu'],
            [
                'name' => 'Prof. Liza Santos',
                'password' => 'password123',
                'role' => 'faculty',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'student@school.edu'],
            [
                'name' => 'Juan Miguel Reyes',
                'password' => 'password123',
                'role' => 'student',
            ],
        );

        $moduleTemplates = [
            [
                'module_name' => 'Advanced Mathematics',
                'module_code' => 'MATH301',
                'instructor' => 'Dr. Maria Fernandez',
                'schedule' => 'Mon, Wed, Fri 9:00 AM',
                'grade_percent' => 87,
                'documents_count' => 1,
                'upcoming_assessment_title' => 'Algebra Quiz 1',
                'upcoming_assessment_points' => 100,
                'upcoming_assessment_due_date' => today()->addDays(3),
                'upcoming_assessment_duration_minutes' => 45,
            ],
            [
                'module_name' => 'Physics I',
                'module_code' => 'PHY201',
                'instructor' => 'Mr. Paulo Navarro',
                'schedule' => 'Tue, Thu 10:00 AM',
                'grade_percent' => 83,
                'documents_count' => 1,
                'upcoming_assessment_title' => "Newton's Laws Quiz",
                'upcoming_assessment_points' => 100,
                'upcoming_assessment_due_date' => today()->addDays(8),
                'upcoming_assessment_duration_minutes' => 30,
            ],
            [
                'module_name' => 'World History',
                'module_code' => 'HIST201',
                'instructor' => 'Mrs. Grace Bautista',
                'schedule' => 'Mon, Wed 2:00 PM',
                'grade_percent' => 91,
                'documents_count' => 0,
                'upcoming_assessment_title' => null,
                'upcoming_assessment_points' => null,
                'upcoming_assessment_due_date' => null,
                'upcoming_assessment_duration_minutes' => null,
            ],
        ];

        $students = User::query()
            ->where('role', 'student')
            ->get();

        foreach ($students as $student) {
            foreach ($moduleTemplates as $moduleTemplate) {
                StudentModuleRecord::withoutEvents(function () use ($student, $moduleTemplate) {
                    StudentModuleRecord::query()->updateOrCreate(
                        [
                            'user_id' => $student->id,
                            'module_code' => $moduleTemplate['module_code'],
                        ],
                        $moduleTemplate,
                    );
                });
            }
        }

        $attendanceTemplates = [
            [
                'student_name' => 'Miguel Santos',
                'student_class' => '10th A',
                'attendance_date' => today()->subDays(5)->toDateString(),
                'status' => 'Present',
            ],
            [
                'student_name' => 'Andrea Reyes',
                'student_class' => '10th A',
                'attendance_date' => today()->subDays(5)->toDateString(),
                'status' => 'Present',
            ],
            [
                'student_name' => 'Carlo Dela Cruz',
                'student_class' => '10th B',
                'attendance_date' => today()->subDays(5)->toDateString(),
                'status' => 'Late',
            ],
            [
                'student_name' => 'Bea Villanueva',
                'student_class' => '11th A',
                'attendance_date' => today()->subDays(4)->toDateString(),
                'status' => 'Absent',
            ],
        ];

        $facultyMembers = User::query()
            ->where('role', 'faculty')
            ->get();

        foreach ($facultyMembers as $facultyMember) {
            foreach ($attendanceTemplates as $attendanceTemplate) {
                FacultyAttendanceRecord::query()->updateOrCreate(
                    [
                        'faculty_user_id' => $facultyMember->id,
                        'student_name' => $attendanceTemplate['student_name'],
                        'attendance_date' => $attendanceTemplate['attendance_date'],
                    ],
                    $attendanceTemplate,
                );
            }
        }
    }
}