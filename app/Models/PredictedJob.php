<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PredictedJob extends Model
{
    protected $fillable = [
        'sequence_hash',
        'job_id',
        'fasta_sequence',
        'fasta_filename',
        'fasta_preview',
        'protein_name',
        'organism',
        'uniprot_id',
        'pdb_id',
        'sequence_length',
        'plddt_mean',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'plddt_mean' => 'float',
            'sequence_length' => 'integer',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function displayName(): string
    {
        return $this->protein_name ?: ('job '.substr((string) $this->job_id, 0, 8));
    }
}
