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
        Schema::create('remote_users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('domain');
            $table->string('display_name')->nullable();
            $table->string('institution')->nullable();
            $table->foreignId('federation_instance_id')->constrained('federation_instances')->cascadeOnDelete();
            $table->string('avatar_url')->nullable();
            $table->timestamps();

            $table->unique(['username', 'domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remote_users');
    }
};
