<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classroom_grading_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('component_name');          // e.g. Quiz, Assignment, Exam, Participation
            $table->decimal('weight', 5, 2);           // percentage weight (e.g. 30.00)
            $table->string('term')->default('Prelim'); // Prelim, Midterm, Final
            $table->timestamps();

            $table->unique(['classroom_id', 'component_name', 'term'], 'cgc_classroom_component_term_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_grading_criteria');
    }
};
