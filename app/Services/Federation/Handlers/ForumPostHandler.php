<?php

namespace App\Services\Federation\Handlers;

use App\Models\Federation\FederationInstance;
use App\Models\Federation\RemoteUser;
use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumThread;

class ForumPostHandler
{
    /**
     * Handle an inbound federated forum post activity.
     *
     * @param  array{author: array{username: string, domain: string, display_name?: string}, body: string, origin_id: string, origin_domain: string, thread_origin_id: string, thread_origin_domain: string, parent_origin_id?: string}  $payload
     */
    public function handle(array $payload): void
    {
        $authorData = $payload['author'];

        $instance = FederationInstance::where('domain', $authorData['domain'])->first();

        $remoteUser = RemoteUser::firstOrCreate(
            [
                'username' => $authorData['username'],
                'domain' => $authorData['domain'],
            ],
            [
                'display_name' => $authorData['display_name'] ?? $authorData['username'],
                'federation_instance_id' => $instance?->id,
            ],
        );

        $thread = ForumThread::where('origin_domain', $payload['thread_origin_domain'])
            ->where('origin_id', $payload['thread_origin_id'])
            ->first();

        if (! $thread) {
            return;
        }

        $parentId = null;

        if (! empty($payload['parent_origin_id'])) {
            $parent = ForumPost::where('origin_id', $payload['parent_origin_id'])
                ->where('origin_domain', $payload['origin_domain'])
                ->first();

            $parentId = $parent?->id;
        }

        ForumPost::firstOrCreate(
            [
                'origin_domain' => $payload['origin_domain'],
                'origin_id' => $payload['origin_id'],
            ],
            [
                'forum_thread_id' => $thread->id,
                'body' => $payload['body'],
                'remote_user_id' => $remoteUser->id,
                'parent_id' => $parentId,
            ],
        );

        $thread->increment('posts_count');
        $thread->update(['last_activity_at' => now()]);
    }
}
