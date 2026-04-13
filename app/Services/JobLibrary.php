<?php

namespace App\Services;

use App\Models\PredictedJob;
use App\Services\Federation\ActivityDispatcher;

class JobLibrary
{
    public function findByFasta(string $fasta): ?PredictedJob
    {
        return PredictedJob::where('sequence_hash', $this->hashFasta($fasta))->first();
    }

    public function findByJobId(string $jobId): ?PredictedJob
    {
        return PredictedJob::where('job_id', $jobId)->first();
    }

    /**
     * Called after a successful submission to CESGA. Creates or updates the
     * library row keyed by sequence hash so future submissions of the same
     * sequence can be deduplicated. The metadata (protein_name, plddt_mean,
     * etc.) is filled later by enrichFromOutputs when the job finishes.
     */
    public function registerSubmission(string $fasta, string $jobId, ?string $fastaFilename = null): PredictedJob
    {
        $hash = $this->hashFasta($fasta);
        $normalized = $this->normalize($fasta);

        return PredictedJob::updateOrCreate(
            ['sequence_hash' => $hash],
            [
                'job_id' => $jobId,
                'fasta_sequence' => $fasta,
                'fasta_filename' => $fastaFilename,
                'fasta_preview' => substr($normalized, 0, 200),
                'sequence_length' => strlen($normalized),
                // Clear metadata so a rerun shows "pending" until enrichment.
                'protein_name' => null,
                'organism' => null,
                'uniprot_id' => null,
                'pdb_id' => null,
                'plddt_mean' => null,
                'completed_at' => null,
            ]
        );
    }

    /**
     * Called whenever /jobs/{id} loads a COMPLETED job. Fills in metadata
     * scraped from the CESGA outputs response. Idempotent: safe to call
     * on every page load.
     *
     * @param  array<string, mixed>  $outputs
     */
    public function enrichFromOutputs(string $jobId, array $outputs): ?PredictedJob
    {
        $row = $this->findByJobId($jobId);
        if ($row === null) {
            return null;
        }

        $meta = $outputs['protein_metadata'] ?? [];
        $confidence = $outputs['structural_data']['confidence'] ?? [];

        $row->fill([
            'protein_name' => $meta['protein_name'] ?? $row->protein_name,
            'organism' => $meta['organism'] ?? $row->organism,
            'uniprot_id' => $meta['uniprot_id'] ?? $row->uniprot_id,
            'pdb_id' => $meta['pdb_id'] ?? $row->pdb_id,
            'plddt_mean' => $confidence['plddt_mean'] ?? $row->plddt_mean,
            'completed_at' => $row->completed_at ?? now(),
        ]);
        $row->save();

        return $row;
    }

    public function shareEntry(PredictedJob $job): void
    {
        $job->update(['is_shared' => true]);

        if (config('services.federation.enabled')) {
            app(ActivityDispatcher::class)->dispatch(
                type: 'library_entry',
                action: 'create',
                payload: [
                    'protein_name' => $job->protein_name,
                    'organism' => $job->organism,
                    'uniprot_id' => $job->uniprot_id,
                    'pdb_id' => $job->pdb_id,
                    'sequence_length' => $job->sequence_length,
                    'plddt_mean' => $job->plddt_mean,
                    'fasta_preview' => $job->fasta_preview,
                    'origin_id' => (string) $job->id,
                ],
            );
        }
    }

    public function deleteByJobId(string $jobId): void
    {
        PredictedJob::where('job_id', $jobId)->delete();
    }

    public function hashFasta(string $fasta): string
    {
        return hash('sha256', $this->normalize($fasta));
    }

    /**
     * Canonical representation of a FASTA sequence used for deduplication.
     * Strips header lines (">..."), removes all whitespace, uppercases.
     */
    private function normalize(string $fasta): string
    {
        $stripped = preg_replace('/^>.*$/m', '', $fasta) ?? '';
        $stripped = preg_replace('/\s+/', '', $stripped) ?? '';

        return strtoupper($stripped);
    }
}
