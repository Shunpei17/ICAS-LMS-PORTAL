<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['classrooms', 'grades', 'faculty_attendance_records', 'student_module_records'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('academic_year', 20)->nullable()->after('id');
                $table->string('semester', 50)->nullable()->after('academic_year');
            });
        }
    }

    public function down(): void
    {
        $tables = ['classrooms', 'grades', 'faculty_attendance_records', 'student_module_records'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['academic_year', 'semester']);
            });
        }
    }
};
