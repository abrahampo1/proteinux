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
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->text('body');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('remote_user_id')->nullable()->constrained('remote_users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('forum_posts')->cascadeOnDelete();
            $table->string('origin_domain', 255)->nullable();
            $table->string('origin_id', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
    }
};
