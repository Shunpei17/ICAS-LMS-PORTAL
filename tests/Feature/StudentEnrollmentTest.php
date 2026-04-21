<?php

use App\Models\StudentModuleRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('allows students to open the enrollment page', function () {
    $student = User::factory()->create(['role' => 'student']);

    actingAs($student)
        ->get(route('student.enrollment'))
        ->assertSuccessful()
        ->assertSee('Enrollment Center');
});

it('allows students to enroll in an available module', function () {
    $student = User::factory()->create(['role' => 'student']);

    actingAs($student)
        ->post(route('student.enrollment.store'), [
            'module_code' => 'MATH301',
        ])
        ->assertRedirect(route('student.enrollment'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('student_module_records', [
        'user_id' => $student->id,
        'module_code' => 'MATH301',
        'module_name' => 'Advanced Mathematics',
    ]);
});

it('prevents duplicate module enrollment for the same student', function () {
    $student = User::factory()->create(['role' => 'student']);

    StudentModuleRecord::query()->create([
        'user_id' => $student->id,
        'module_name' => 'Advanced Mathematics',
        'module_code' => 'MATH301',
        'instructor' => 'Dr. Maria Fernandez',
        'schedule' => 'Mon, Wed, Fri 9:00 AM',
        'documents_count' => 0,
    ]);

    actingAs($student)
        ->post(route('student.enrollment.store'), [
            'module_code' => 'MATH301',
        ])
        ->assertSessionHasErrors(['module_code']);
});

it('rejects module codes outside the enrollment catalog', function () {
    $student = User::factory()->create(['role' => 'student']);

    actingAs($student)
        ->post(route('student.enrollment.store'), [
            'module_code' => 'UNKNOWN500',
        ])
        ->assertSessionHasErrors(['module_code']);
});

it('redirects non-student users away from enrollment routes', function () {
    $faculty = User::factory()->create(['role' => 'faculty']);

    actingAs($faculty)
        ->get(route('student.enrollment'))
        ->assertRedirect(route('faculty.dashboard'));
});
