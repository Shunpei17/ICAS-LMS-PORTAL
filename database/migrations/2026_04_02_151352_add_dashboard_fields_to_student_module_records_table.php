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
        Schema::table('student_module_records', function (Blueprint $table) {
            if (! Schema::hasColumn('student_module_records', 'grade_percent')) {
                $table->decimal('grade_percent', 5, 2)->unsigned()->nullable()->after('schedule');
            }

            if (! Schema::hasColumn('student_module_records', 'documents_count')) {
                $table->unsignedInteger('documents_count')->default(0)->after('grade_percent');
            }

            if (! Schema::hasColumn('student_module_records', 'upcoming_assessment_title')) {
                $table->string('upcoming_assessment_title')->nullable()->after('documents_count');
            }

            if (! Schema::hasColumn('student_module_records', 'upcoming_assessment_points')) {
                $table->unsignedSmallInteger('upcoming_assessment_points')->nullable()->after('upcoming_assessment_title');
            }

            if (! Schema::hasColumn('student_module_records', 'upcoming_assessment_due_date')) {
                $table->date('upcoming_assessment_due_date')->nullable()->after('upcoming_assessment_points');
            }

            if (! Schema::hasColumn('student_module_records', 'upcoming_assessment_duration_minutes')) {
                $table->unsignedSmallInteger('upcoming_assessment_duration_minutes')->nullable()->after('upcoming_assessment_due_date');
            }
        });

        Schema::table('student_module_records', function (Blueprint $table) {
            $table->index(['user_id', 'upcoming_assessment_due_date'], 'student_module_records_user_due_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_module_records', function (Blueprint $table) {
            $table->dropIndex('student_module_records_user_due_index');
        });

        Schema::table('student_module_records', function (Blueprint $table) {
            if (Schema::hasColumn('student_module_records', 'upcoming_assessment_duration_minutes')) {
                $table->dropColumn('upcoming_assessment_duration_minutes');
            }

            if (Schema::hasColumn('student_module_records', 'upcoming_assessment_due_date')) {
                $table->dropColumn('upcoming_assessment_due_date');
            }

            if (Schema::hasColumn('student_module_records', 'upcoming_assessment_points')) {
                $table->dropColumn('upcoming_assessment_points');
            }

            if (Schema::hasColumn('student_module_records', 'upcoming_assessment_title')) {
                $table->dropColumn('upcoming_assessment_title');
            }

            if (Schema::hasColumn('student_module_records', 'documents_count')) {
                $table->dropColumn('documents_count');
            }

            if (Schema::hasColumn('student_module_records', 'grade_percent')) {
                $table->dropColumn('grade_percent');
            }
        });
    }
};
