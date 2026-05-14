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
        Schema::table('users', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('users', 'account_source')) {
                $table->dropColumn('account_source');
            }
            if (Schema::hasColumn('users', 'force_password_change')) {
                $table->dropColumn('force_password_change');
            }

            // Add new columns with specific names
            $table->string('registration_source')->default('manual')->after('status');
            $table->boolean('force_password_reset')->default(false)->after('registration_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('registration_source');
            $table->dropColumn('force_password_reset');
            
            // Restore old columns (as enum)
            $table->enum('account_source', ['csv_import', 'manual_registration', 'admin_created'])->default('manual_registration')->after('status');
            $table->boolean('force_password_change')->default(false)->after('account_source');
        });
    }
};
