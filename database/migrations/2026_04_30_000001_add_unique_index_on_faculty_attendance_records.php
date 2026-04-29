<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove exact duplicate records (keep the latest entry)
        $duplicates = DB::select(<<<'SQL'
            SELECT id FROM (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY student_name, student_class, attendance_date ORDER BY id DESC) AS rn
                FROM faculty_attendance_records
            ) t WHERE t.rn > 1
        SQL
        );

        if (! empty($duplicates)) {
            $ids = array_map(fn($r) => $r->id, $duplicates);
            DB::table('faculty_attendance_records')->whereIn('id', $ids)->delete();
        }

        Schema::table('faculty_attendance_records', function (Blueprint $table): void {
            $table->unique(['student_name', 'student_class', 'attendance_date'], 'fac_attendance_unique');
        });
    }

    public function down(): void
    {
        Schema::table('faculty_attendance_records', function (Blueprint $table): void {
            $table->dropUnique('fac_attendance_unique');
        });
    }
};
