<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('faculty_attendance_records', function (Blueprint $table) {
            $table->unsignedBigInteger('student_user_id')->nullable()->after('faculty_user_id');
        });

        // Best-effort population: match by exact name (case-sensitive)
        // More aggressive matching (email-based, fuzzy) is done via the artisan command attendance:map-students
        DB::statement(<<<'SQL'
            UPDATE faculty_attendance_records
            SET student_user_id = (
                SELECT u.id FROM users u
                WHERE u.name = faculty_attendance_records.student_name
                LIMIT 1
            )
            WHERE student_user_id IS NULL
        SQL
        );

        // Drop old unique index on name/class/date if it exists, then add new unique index on user_id/class/date
        Schema::table('faculty_attendance_records', function (Blueprint $table) {
            // attempt to drop index by name if present
            try {
                $table->dropUnique('fac_attendance_unique');
            } catch (Exception $e) {
                // ignore if index does not exist
            }

            $table->unique(['student_user_id', 'student_class', 'attendance_date'], 'fac_attendance_unique_by_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_attendance_records', function (Blueprint $table) {
            try {
                $table->dropUnique('fac_attendance_unique_by_user');
            } catch (Exception $e) {
            }

            // recreate old unique index on name/class/date if needed
            try {
                $table->unique(['student_name', 'student_class', 'attendance_date'], 'fac_attendance_unique');
            } catch (Exception $e) {
            }

            $table->dropColumn('student_user_id');
        });
    }
};
