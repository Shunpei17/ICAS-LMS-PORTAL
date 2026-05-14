<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_image_mime')->nullable();
            $table->string('receipt_proof_mime')->nullable();
            $table->string('student_id_proof_mime')->nullable();
            
            if (!Schema::hasColumn('users', 'academic_level')) {
                $table->string('academic_level')->nullable();
            }
            if (!Schema::hasColumn('users', 'course')) {
                $table->string('course')->nullable();
            }
        });

        // Use raw SQL for LONGBLOB
        DB::statement('ALTER TABLE users ADD profile_image_blob LONGBLOB NULL');
        DB::statement('ALTER TABLE users ADD receipt_proof_blob LONGBLOB NULL');
        DB::statement('ALTER TABLE users ADD student_id_proof_blob LONGBLOB NULL');

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('attachment_mime')->nullable();
            $table->string('attachment_filename')->nullable();
        });
        
        DB::statement('ALTER TABLE announcements ADD attachment_blob LONGBLOB NULL');

        Schema::table('faculty_attendance_records', function (Blueprint $table) {
            $table->string('course_strand')->nullable()->after('student_class');
            $table->string('academic_level')->nullable()->after('course_strand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_image_mime',
                'receipt_proof_mime',
                'student_id_proof_mime',
                'profile_image_blob',
                'receipt_proof_blob',
                'student_id_proof_blob'
            ]);
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['attachment_blob', 'attachment_mime', 'attachment_filename']);
        });

        Schema::table('faculty_attendance_records', function (Blueprint $table) {
            $table->dropColumn(['course_strand', 'academic_level']);
        });
    }
};
