<?php

namespace App\Models\Forum;

use App\Models\PredictedJob;
use App\Traits\HasFederatedAuthor;
use Database\Factories\Forum\ForumThreadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ForumThread extends Model
{
    /** @use HasFactory<ForumThreadFactory> */
    use HasFactory, HasFederatedAuthor;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'user_id',
        'remote_user_id',
        'predicted_job_id',
        'protein_reference',
        'is_pinned',
        'is_locked',
        'origin_domain',
        'origin_id',
        'posts_count',
        'last_activity_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    /**
     * Use slug as the route key.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<ForumPost, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    /**
     * @return BelongsTo<PredictedJob, $this>
     */
    public function predictedJob(): BelongsTo
    {
        return $this->belongsTo(PredictedJob::class);
    }

    /**
     * Check whether this thread originated from a remote instance.
     */
    public function isRemote(): bool
    {
        return $this->origin_domain !== null;
    }

    /**
     * Scope threads linked to a specific predicted job.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForPredictedJob($query, int $predictedJobId)
    {
        return $query->where('predicted_job_id', $predictedJobId);
    }

    /**
     * Scope threads linked to a specific protein reference.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForProteinReference($query, string $proteinReference)
    {
        return $query->where('protein_reference', $proteinReference);
    }

    protected static function booted(): void
    {
        static::creating(function (ForumThread $thread): void {
            if (empty($thread->slug)) {
                $baseSlug = Str::slug($thread->title);
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $thread->slug = $slug;
            }

            if ($thread->last_activity_at === null) {
                $thread->last_activity_at = now();
            }
        });
    }

    protected static function newFactory(): ForumThreadFactory
    {
        return ForumThreadFactory::new();
    }
}
