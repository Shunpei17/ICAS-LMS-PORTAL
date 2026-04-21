<?php

use App\Models\StudentModuleRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('allows admins to download the grade generator csv', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['role' => 'student', 'name' => 'Aira Ramos', 'email' => 'aira@example.test']);

    StudentModuleRecord::query()->create([
        'user_id' => $student->id,
        'module_name' => 'Advanced Mathematics',
        'module_code' => 'MATH301',
        'instructor' => 'Dr. Maria Fernandez',
        'schedule' => 'Mon, Wed, Fri 9:00 AM',
        'grade_percent' => 91.5,
        'documents_count' => 0,
    ]);

    $response = actingAs($admin)->get(route('admin.grades.export'));

    $response
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect((string) $response->headers->get('content-disposition'))
        ->toStartWith('attachment; filename=grade-generator-')
        ->toContain('.csv');

    $content = $response->streamedContent();
    $rows = array_values(array_filter(preg_split('/\r\n|\r|\n/', trim($content)) ?: []));

    $header = str_getcsv(ltrim((string) ($rows[0] ?? ''), "\xEF\xBB\xBF"));
    $firstDataRow = str_getcsv((string) ($rows[1] ?? ''));

    expect($header)->toBe([
        'Student Name',
        'Student Email',
        'Module Name',
        'Module Code',
        'Instructor',
        'Schedule',
        'Grade Percent',
    ]);

    expect($firstDataRow)
        ->toContain('Aira Ramos')
        ->toContain('aira@example.test')
        ->toContain('Advanced Mathematics')
        ->toContain('91.50');
});

it('redirects non-admin users away from the grade generator download', function () {
    $faculty = User::factory()->create(['role' => 'faculty']);

    actingAs($faculty)
        ->get(route('admin.grades.export'))
        ->assertRedirect(route('faculty.dashboard'));
});

it('redirects guests to login when trying to download grade generator csv', function () {
    $this->get(route('admin.grades.export'))
        ->assertRedirect(route('login'));
});
