<?php

namespace App\Services\Federation\Handlers;

use App\Models\Federation\FederationInstance;
use App\Models\Federation\RemoteUser;
use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumThread;

class ForumPostHandler
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $data = $payload['data'] ?? $payload;
        $originDomain = $payload['source_domain'] ?? ($data['origin_domain'] ?? null);
        $originId = $data['origin_id'] ?? null;
        $authorData = $data['author'] ?? [];

        if (! $originDomain || ! $originId) {
            return;
        }

        // Deduplicate
        if (ForumPost::where('origin_domain', $originDomain)->where('origin_id', $originId)->exists()) {
            return;
        }

        $instance = FederationInstance::where('domain', $authorData['domain'] ?? $originDomain)->first();

        $remoteUser = RemoteUser::firstOrCreate(
            [
                'username' => $authorData['username'] ?? 'unknown',
                'domain' => $authorData['domain'] ?? $originDomain,
            ],
            [
                'display_name' => $authorData['display_name'] ?? ($authorData['username'] ?? 'unknown'),
                'federation_instance_id' => $instance?->id,
            ],
        );

        $thread = ForumThread::where('origin_domain', $data['thread_origin_domain'] ?? null)
            ->where('origin_id', $data['thread_origin_id'] ?? null)
            ->first();

        if (! $thread) {
            return;
        }

        $parentId = null;

        if (! empty($data['parent_origin_id'])) {
            $parent = ForumPost::where('origin_id', $data['parent_origin_id'])
                ->where('origin_domain', $originDomain)
                ->first();

            $parentId = $parent?->id;
        }

        ForumPost::create([
            'forum_thread_id' => $thread->id,
            'body' => $data['body'] ?? '',
            'remote_user_id' => $remoteUser->id,
            'parent_id' => $parentId,
            'origin_domain' => $originDomain,
            'origin_id' => $originId,
        ]);

        $thread->increment('posts_count');
        $thread->update(['last_activity_at' => now()]);
    }
}
