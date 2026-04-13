<?php

namespace App\Models\Federation;

use Database\Factories\Federation\FederationInstanceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FederationInstance extends Model
{
    /** @use HasFactory<FederationInstanceFactory> */
    use HasFactory;

    protected $fillable = [
        'domain',
        'name',
        'description',
        'public_key',
        'status',
        'last_seen_at',
        'last_synced_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'last_seen_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<RemoteUser, $this>
     */
    public function remoteUsers(): HasMany
    {
        return $this->hasMany(RemoteUser::class);
    }

    /**
     * @return HasMany<FederationActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(FederationActivity::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    protected static function newFactory(): FederationInstanceFactory
    {
        return FederationInstanceFactory::new();
    }
}
