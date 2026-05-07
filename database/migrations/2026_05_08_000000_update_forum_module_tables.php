<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_threads', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('status');
            }
            if (!Schema::hasColumn('forum_threads', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false)->after('is_visible');
            }
        });

        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_replies');
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropColumn(['is_visible', 'is_flagged']);
        });
    }
};
