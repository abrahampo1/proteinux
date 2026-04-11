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
        Schema::create('predicted_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('sequence_hash', 64)->unique();
            $table->string('job_id')->index();
            $table->text('fasta_sequence');
            $table->string('fasta_filename')->nullable();
            $table->text('fasta_preview')->nullable();
            $table->string('protein_name')->nullable();
            $table->string('organism')->nullable();
            $table->string('uniprot_id')->nullable();
            $table->string('pdb_id')->nullable();
            $table->unsignedInteger('sequence_length')->nullable();
            $table->float('plddt_mean')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predicted_jobs');
    }
};
