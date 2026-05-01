<?php

use App\Models\Classroom;
use App\Models\User;

it('blocks student enrollment into inactive classroom via middleware', function () {
    $student = User::factory()->create(['role' => 'student']);
    $classroom = Classroom::create([
        'faculty_user_id' => 1,
        'name' => 'Test Room',
        'code' => 'TEST101',
        'status' => 'inactive',
    ]);

    $this->actingAs($student)
        ->post(route('student.classrooms.enroll', $classroom))
        ->assertStatus(403);
});

it('prevents faculty from recording attendance for inactive classroom via policy', function () {
    $faculty = User::factory()->create(['role' => 'faculty']);
    $classroom = Classroom::create([
        'faculty_user_id' => $faculty->id,
        'name' => 'Test Room',
        'code' => 'TEST201',
        'status' => 'inactive',
    ]);

    $this->actingAs($faculty)
        ->post(route('faculty.grades.records.store'), [
            'student_name' => 'Jane Doe',
            'student_class' => $classroom->code,
            'attendance_date' => now()->toDateString(),
            'status' => 'Present',
        ])
        ->assertStatus(403);
});
