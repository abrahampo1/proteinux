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
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('body');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('remote_user_id')->nullable()->constrained('remote_users')->nullOnDelete();
            $table->foreignId('predicted_job_id')->nullable()->constrained('predicted_jobs')->nullOnDelete();
            $table->string('protein_reference', 255)->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->string('origin_domain', 255)->nullable();
            $table->string('origin_id', 255)->nullable();
            $table->unsignedInteger('posts_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_threads');
    }
};
