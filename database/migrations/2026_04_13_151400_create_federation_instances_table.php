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
        Schema::create('federation_instances', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('public_key')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('federation_instances');
    }
};
