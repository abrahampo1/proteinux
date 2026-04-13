<?php

namespace App\Services\Federation\Handlers;

use App\Models\PredictedJob;

class LibraryEntryHandler
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $data = $payload['data'] ?? $payload;
        $originDomain = $payload['source_domain'] ?? ($payload['origin']['domain'] ?? null);
        $originId = $data['origin_id'] ?? null;

        if (! $originDomain || ! $originId) {
            return;
        }

        // Deduplicate
        $existing = PredictedJob::where('origin_domain', $originDomain)
            ->where('origin_id', $originId)
            ->first();

        if ($existing) {
            return;
        }

        PredictedJob::create([
            'sequence_hash' => hash('sha256', $originDomain.':'.$originId),
            'job_id' => 'remote-'.$originDomain.'-'.$originId,
            'fasta_sequence' => '',
            'fasta_preview' => $data['fasta_preview'] ?? null,
            'protein_name' => $data['protein_name'] ?? null,
            'organism' => $data['organism'] ?? null,
            'uniprot_id' => $data['uniprot_id'] ?? null,
            'pdb_id' => $data['pdb_id'] ?? null,
            'sequence_length' => $data['sequence_length'] ?? null,
            'plddt_mean' => $data['plddt_mean'] ?? null,
            'completed_at' => now(),
            'is_shared' => false,
            'is_remote' => true,
            'origin_domain' => $originDomain,
            'origin_id' => $originId,
        ]);
    }
}
