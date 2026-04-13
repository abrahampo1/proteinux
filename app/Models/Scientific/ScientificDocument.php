<?php

namespace App\Models\Scientific;

use App\Models\PredictedJob;
use App\Traits\HasFederatedAuthor;
use Database\Factories\Scientific\ScientificDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ScientificDocument extends Model
{
    /** @use HasFactory<ScientificDocumentFactory> */
    use HasFactory, HasFederatedAuthor;

    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'url',
        'doi',
        'user_id',
        'remote_user_id',
        'origin_domain',
        'origin_id',
        'is_shared',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_shared' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<PredictedJob, $this>
     */
    public function proteins(): BelongsToMany
    {
        return $this->belongsToMany(PredictedJob::class, 'scientific_document_protein');
    }

    /**
     * Whether this document originates from a remote federated instance.
     */
    public function isRemote(): bool
    {
        return $this->origin_domain !== null;
    }

    /**
     * Whether this document has an uploaded file attached.
     */
    public function hasFile(): bool
    {
        return $this->file_path !== null;
    }

    /**
     * Return the Spanish label for the document type.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'paper' => 'Articulo',
            'note' => 'Apunte',
            'protocol' => 'Protocolo',
            'dataset' => 'Dataset',
            default => $this->type,
        };
    }

    protected static function newFactory(): ScientificDocumentFactory
    {
        return ScientificDocumentFactory::new();
    }
}
