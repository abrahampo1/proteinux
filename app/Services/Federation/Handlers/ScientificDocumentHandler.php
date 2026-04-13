<?php

namespace App\Services\Federation\Handlers;

use App\Models\Federation\RemoteUser;
use App\Models\Scientific\ScientificDocument;

class ScientificDocumentHandler
{
    /**
     * Handle an inbound federated scientific document activity.
     *
     * Only metadata is stored — files are not downloaded. The `url` field
     * points to the download URL on the origin instance.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $origin = $payload['origin'] ?? [];
        $object = $payload['object'] ?? [];
        $author = $payload['author'] ?? [];

        $originDomain = $origin['domain'] ?? null;
        $originId = $object['origin_id'] ?? null;

        if (! $originDomain || ! $originId) {
            return;
        }

        // Deduplicate by origin
        $existing = ScientificDocument::where('origin_domain', $originDomain)
            ->where('origin_id', $originId)
            ->first();

        if ($existing) {
            return;
        }

        // Find or create the remote user
        $remoteUser = null;

        if (! empty($author['username']) && ! empty($author['domain'])) {
            $remoteUser = RemoteUser::firstOrCreate(
                [
                    'username' => $author['username'],
                    'domain' => $author['domain'],
                ],
                [
                    'display_name' => $author['display_name'] ?? $author['username'],
                    'institution' => $author['institution'] ?? null,
                ]
            );
        }

        ScientificDocument::create([
            'title' => $object['title'] ?? 'Sin titulo',
            'description' => $object['description'] ?? null,
            'type' => $object['type'] ?? 'paper',
            'url' => $object['download_url'] ?? null,
            'doi' => $object['doi'] ?? null,
            'file_name' => $object['file_name'] ?? null,
            'file_size' => $object['file_size'] ?? null,
            'mime_type' => $object['mime_type'] ?? null,
            'remote_user_id' => $remoteUser?->id,
            'origin_domain' => $originDomain,
            'origin_id' => $originId,
            'is_shared' => true,
        ]);
    }
}
