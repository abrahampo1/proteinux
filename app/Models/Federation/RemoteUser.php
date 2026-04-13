<?php

namespace App\Models\Federation;

use Database\Factories\Federation\RemoteUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemoteUser extends Model
{
    /** @use HasFactory<RemoteUserFactory> */
    use HasFactory;

    protected $fillable = [
        'username',
        'domain',
        'display_name',
        'institution',
        'federation_instance_id',
        'avatar_url',
    ];

    /**
     * @return BelongsTo<FederationInstance, $this>
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(FederationInstance::class, 'federation_instance_id');
    }

    /**
     * Return the federated identifier in username@domain format.
     */
    public function federatedId(): string
    {
        return $this->username.'@'.$this->domain;
    }

    protected static function newFactory(): RemoteUserFactory
    {
        return RemoteUserFactory::new();
    }
}
