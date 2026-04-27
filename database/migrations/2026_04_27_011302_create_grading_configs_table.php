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
        Schema::create('grading_configs', function (Blueprint $table) {
            $table->id();
            $table->decimal('quiz_weight', 5, 2)->default(30.00);
            $table->decimal('assignment_weight', 5, 2)->default(30.00);
            $table->decimal('exam_weight', 5, 2)->default(40.00);
            $table->decimal('passing_grade', 5, 2)->default(75.00);
            $table->string('grading_scale')->default('percentage'); // letter, percentage, gpa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_configs');
    }
};
