<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predicted_jobs', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('completed_at');
            $table->foreignId('user_id')->nullable()->after('is_shared')->constrained()->nullOnDelete();
            $table->string('origin_domain')->nullable()->after('user_id');
            $table->string('origin_id')->nullable()->after('origin_domain');
            $table->boolean('is_remote')->default(false)->after('origin_id');

            $table->unique(['origin_domain', 'origin_id'], 'predicted_jobs_origin_unique');
        });
    }

    public function down(): void
    {
        Schema::table('predicted_jobs', function (Blueprint $table) {
            $table->dropUnique('predicted_jobs_origin_unique');
            $table->dropForeign(['user_id']);
            $table->dropColumn(['is_shared', 'user_id', 'origin_domain', 'origin_id', 'is_remote']);
        });
    }
};
