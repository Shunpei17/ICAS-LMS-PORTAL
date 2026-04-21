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
        Schema::create('faculty_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('student_name');
            $table->string('student_class', 50);
            $table->date('attendance_date');
            $table->enum('status', ['Present', 'Absent', 'Late']);
            $table->timestamps();

            $table->index(['faculty_user_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_attendance_records');
    }
};
