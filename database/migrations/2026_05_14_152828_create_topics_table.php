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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('classroom_id')->nullable()->after('topic_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
            $table->dropConstrainedForeignId('classroom_id');
        });
        Schema::dropIfExists('topics');
    }
};
