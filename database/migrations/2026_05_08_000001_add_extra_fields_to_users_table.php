<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'strand')) {
                $table->string('strand')->nullable()->after('course');
            }
            if (!Schema::hasColumn('users', 'admin_number')) {
                $table->string('admin_number')->nullable()->unique()->after('student_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['strand', 'admin_number']);
        });
    }
};
