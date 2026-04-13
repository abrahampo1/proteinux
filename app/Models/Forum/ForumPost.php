<?php

namespace App\Models\Forum;

use App\Traits\HasFederatedAuthor;
use Database\Factories\Forum\ForumPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends Model
{
    /** @use HasFactory<ForumPostFactory> */
    use HasFactory, HasFederatedAuthor;

    protected $fillable = [
        'forum_thread_id',
        'body',
        'user_id',
        'remote_user_id',
        'parent_id',
        'origin_domain',
        'origin_id',
    ];

    /**
     * @return BelongsTo<ForumThread, $this>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    /**
     * @return BelongsTo<ForumPost, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'parent_id');
    }

    /**
     * @return HasMany<ForumPost, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'parent_id');
    }

    /**
     * Check whether this post originated from a remote instance.
     */
    public function isRemote(): bool
    {
        return $this->origin_domain !== null;
    }

    protected static function newFactory(): ForumPostFactory
    {
        return ForumPostFactory::new();
    }
}
