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
        $origin = $payload['origin'] ?? [];
        $object = $payload['object'] ?? [];
        $author = $payload['author'] ?? [];

        $originDomain = $origin['domain'] ?? null;
        $originId = $object['origin_id'] ?? null;

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
            'fasta_preview' => $object['fasta_preview'] ?? null,
            'protein_name' => $object['protein_name'] ?? null,
            'organism' => $object['organism'] ?? null,
            'uniprot_id' => $object['uniprot_id'] ?? null,
            'pdb_id' => $object['pdb_id'] ?? null,
            'sequence_length' => $object['sequence_length'] ?? null,
            'plddt_mean' => $object['plddt_mean'] ?? null,
            'completed_at' => now(),
            'is_shared' => false,
            'is_remote' => true,
            'origin_domain' => $originDomain,
            'origin_id' => $originId,
        ]);
    }
}
