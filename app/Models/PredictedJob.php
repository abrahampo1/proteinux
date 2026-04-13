<?php

namespace App\Models;

use App\Models\Forum\ForumThread;
use App\Models\Scientific\ScientificDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_shared',
        'user_id',
        'origin_domain',
        'origin_id',
        'is_remote',
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
            'is_shared' => 'boolean',
            'is_remote' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function scientificDocuments(): BelongsToMany
    {
        return $this->belongsToMany(
            ScientificDocument::class,
            'scientific_document_protein',
        );
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isRemote(): bool
    {
        return $this->is_remote;
    }

    public function isShared(): bool
    {
        return $this->is_shared;
    }

    public function displayName(): string
    {
        if ($this->protein_name) {
            return $this->protein_name;
        }
        if ($this->fasta_filename) {
            return preg_replace('/\.(fasta|fa|fna|txt)$/i', '', $this->fasta_filename) ?: $this->fasta_filename;
        }

        return 'sin nombre · '.substr((string) $this->job_id, 0, 8);
    }
}
