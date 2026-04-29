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
        Schema::table('faculty_attendance_records', function (Blueprint $table) {
            $table->unsignedBigInteger('student_user_id')->nullable()->after('faculty_user_id');
        });

        // Best-effort population: prefer matching by email, then fallback to matching by name
        // Match by email where student_name appears to contain an email in parentheses: "Name (email@domain)"
        // First, attempt a direct email match if student_name contains an email in parentheses
        DB::statement(<<<'SQL'
            UPDATE faculty_attendance_records far
            JOIN users u ON u.email = SUBSTRING_INDEX(SUBSTRING_INDEX(far.student_name, '(', -1), ')', 1)
            SET far.student_user_id = u.id
            WHERE far.student_user_id IS NULL
              AND far.student_name LIKE '%(%@%)%'
        SQL
        );

        // Next, attempt direct email==student_name match
        DB::statement(<<<'SQL'
            UPDATE faculty_attendance_records far
            JOIN users u ON u.email = far.student_name
            SET far.student_user_id = u.id
            WHERE far.student_user_id IS NULL
        SQL
        );

        // Finally, fallback to matching by name (best-effort)
        DB::statement(<<<'SQL'
            UPDATE faculty_attendance_records far
            LEFT JOIN users u ON u.name = far.student_name
            SET far.student_user_id = u.id
            WHERE far.student_user_id IS NULL
        SQL
        );

        // Drop old unique index on name/class/date if it exists, then add new unique index on user_id/class/date
        Schema::table('faculty_attendance_records', function (Blueprint $table) {
            // attempt to drop index by name if present
            try {
                $table->dropUnique('fac_attendance_unique');
            } catch (\Exception $e) {
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
            } catch (\Exception $e) {
            }

            // recreate old unique index on name/class/date if needed
            try {
                $table->unique(['student_name', 'student_class', 'attendance_date'], 'fac_attendance_unique');
            } catch (\Exception $e) {
            }

            $table->dropColumn('student_user_id');
        });
    }
};
