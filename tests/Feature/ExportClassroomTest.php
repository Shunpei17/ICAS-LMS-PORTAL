<?php

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admin to export classroom csv including student rows', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $faculty = User::factory()->create(['role' => 'faculty']);
    $student = User::factory()->create(['role' => 'student', 'name' => 'Alice Export']);

    $classroom = Classroom::create([
        'faculty_user_id' => $faculty->id,
        'name' => 'Export Room',
        'code' => 'EXP101',
        'status' => 'active',
    ]);

    $classroom->students()->attach($student->id);

    $response = $this->actingAs($admin)
        ->get(route('admin.classrooms.export', $classroom) . '?format=csv');

    $response->assertStatus(200);
    $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
    $this->assertStringContainsString($classroom->code, (string) $response->headers->get('content-disposition'));
});

it('allows assigned faculty to export classroom and blocks unassigned faculty', function () {
    $faculty = User::factory()->create(['role' => 'faculty']);
    $otherFaculty = User::factory()->create(['role' => 'faculty']);

    $classroom = Classroom::create([
        'faculty_user_id' => $faculty->id,
        'name' => 'Faculty Export Room',
        'code' => 'FEXP201',
        'status' => 'active',
    ]);

    // Assigned faculty can export
    $this->actingAs($faculty)
        ->get('/faculty/classrooms/'.$classroom->id.'/export?format=csv')
        ->assertStatus(200);

    // Other faculty cannot export (403)
    $this->actingAs($otherFaculty)
        ->get('/faculty/classrooms/'.$classroom->id.'/export?format=csv')
        ->assertStatus(403);
});

it('returns a PDF content when requested', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $faculty = User::factory()->create(['role' => 'faculty']);

    $classroom = Classroom::create([
        'faculty_user_id' => $faculty->id,
        'name' => 'PDF Room',
        'code' => 'PDF101',
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.classrooms.export', $classroom) . '?format=pdf');

    $response->assertStatus(200);
    $this->assertStringContainsString('pdf', $response->headers->get('content-type'));
    $this->assertGreaterThan(0, strlen($response->getContent()));
});

it('exports xlsx when requested (skips if ext-gd missing)', function () {
    if (!extension_loaded('gd')) {
        $this->markTestSkipped('ext-gd not available, skipping xlsx export assertion.');
    }

    $admin = User::factory()->create(['role' => 'admin']);
    $faculty = User::factory()->create(['role' => 'faculty']);
    $student = User::factory()->create(['role' => 'student', 'name' => 'Bob XLSX']);

    $classroom = Classroom::create([
        'faculty_user_id' => $faculty->id,
        'name' => 'XLSX Room',
        'code' => 'XLSX101',
        'status' => 'active',
    ]);

    $classroom->students()->attach($student->id);

    $response = $this->actingAs($admin)
        ->get(route('admin.classrooms.export', $classroom) . '?format=xlsx');

    $response->assertStatus(200);
    $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $response->headers->get('content-type'));
});
