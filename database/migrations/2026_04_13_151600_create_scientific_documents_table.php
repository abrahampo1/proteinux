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
        Schema::create('scientific_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('type', 50); // paper|note|protocol|dataset
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('doi', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('remote_user_id')->nullable()->constrained('remote_users')->nullOnDelete();
            $table->string('origin_domain', 255)->nullable();
            $table->string('origin_id', 255)->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index(['origin_domain', 'origin_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scientific_documents');
    }
};
