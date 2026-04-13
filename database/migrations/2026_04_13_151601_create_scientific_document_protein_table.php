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
        Schema::create('scientific_document_protein', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scientific_document_id')->constrained('scientific_documents')->cascadeOnDelete();
            $table->foreignId('predicted_job_id')->constrained('predicted_jobs')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['scientific_document_id', 'predicted_job_id'], 'doc_protein_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scientific_document_protein');
    }
};
