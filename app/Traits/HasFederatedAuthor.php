<?php

namespace App\Traits;

use App\Models\Federation\RemoteUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasFederatedAuthor
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<RemoteUser, $this>
     */
    public function remoteUser(): BelongsTo
    {
        return $this->belongsTo(RemoteUser::class);
    }

    /**
     * Return the author, preferring local user over remote user.
     */
    public function author(): User|RemoteUser|null
    {
        return $this->user ?? $this->remoteUser;
    }

    /**
     * Return the display name for the author.
     */
    public function authorDisplayName(): string
    {
        $author = $this->author();

        if ($author instanceof User) {
            return $author->name ?? $author->username;
        }

        if ($author instanceof RemoteUser) {
            return $author->display_name ?? $author->username;
        }

        return 'anon';
    }

    /**
     * Return the federated identifier in username@domain format.
     */
    public function authorFederatedId(): string
    {
        $author = $this->author();

        if ($author instanceof User) {
            return $author->federatedId();
        }

        if ($author instanceof RemoteUser) {
            return $author->federatedId();
        }

        return 'desconocido@local';
    }

    /**
     * Check whether the content was authored by a remote user.
     */
    public function isFromRemoteUser(): bool
    {
        return $this->remote_user_id !== null;
    }
}
