<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faculty_attendance_records', function (Blueprint $table) {
            // add subject_code to identify the subject/module for the attendance row
            $table->string('subject_code', 50)->nullable()->after('faculty_user_id');
        });

        // Replace older unique index with the new composite unique index that includes subject_code
        try {
            Schema::table('faculty_attendance_records', function (Blueprint $table): void {
                try {
                    $table->dropUnique('fac_attendance_unique_by_user');
                } catch (\Exception $e) {
                    // ignore if not present
                }

                try {
                    $table->dropUnique('fac_attendance_unique');
                } catch (\Exception $e) {
                }

                $table->unique(['student_user_id', 'attendance_date', 'faculty_user_id', 'subject_code'], 'fac_attendance_unique_idx');
            });
        } catch (\Exception $e) {
            // ignore schema errors on platforms where index management differs (tests may use sqlite)
        }
    }

    public function down(): void
    {
        Schema::table('faculty_attendance_records', function (Blueprint $table): void {
            try {
                $table->dropUnique('fac_attendance_unique_idx');
            } catch (\Exception $e) {
            }

            // attempt to restore previous unique index
            try {
                $table->unique(['student_user_id', 'student_class', 'attendance_date'], 'fac_attendance_unique_by_user');
            } catch (\Exception $e) {
            }

            $table->dropColumn('subject_code');
        });
    }
};
