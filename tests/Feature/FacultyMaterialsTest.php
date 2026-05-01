<?php

use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// uses(TestCase::class, RefreshDatabase::class); // Commented out to avoid duplication

it('shows add materials button to faculty and admin only', function () {
    $faculty = User::factory()->create(['role' => 'faculty']);
    $student = User::factory()->create(['role' => 'student']);

    // Faculty should see the button
    $this->actingAs($faculty)
        ->get(route('faculty.students.show', 'math301'))
        ->assertSee('+ Add Materials');

    // Student should not see the button
    $this->actingAs($student)
        ->get(route('faculty.students.show', 'math301'))
        ->assertDontSee('+ Add Materials');
});

it('allows faculty to upload a material and stores file and db record', function () {
    Storage::fake('public');

    $faculty = User::factory()->create(['role' => 'faculty']);

    $file = UploadedFile::fake()->create('notes.pdf', 100);

    $response = $this->actingAs($faculty)->postJson(route('faculty.materials.store'), [
        'subject_slug' => 'math301',
        'topic_index' => 0,
        'title' => 'Integration Notes',
        'body' => 'Some notes',
        'type' => 'material',
        'file' => $file,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('materials', [
        'title' => 'Integration Notes',
        'subject_slug' => 'math301',
    ]);

    $material = Material::where('title', 'Integration Notes')->first();
    expect($material)->not->toBeNull();
    Storage::disk('public')->assertExists($material->file_path);
});
