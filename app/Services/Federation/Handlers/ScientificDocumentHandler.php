<?php

namespace App\Services\Federation\Handlers;

use App\Models\Federation\RemoteUser;
use App\Models\Scientific\ScientificDocument;

class ScientificDocumentHandler
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $data = $payload['data'] ?? $payload;
        $originDomain = $payload['source_domain'] ?? ($payload['origin']['domain'] ?? null);
        $originId = $data['origin_id'] ?? null;
        $authorData = $data['author'] ?? [];

        if (! $originDomain || ! $originId) {
            return;
        }

        // Deduplicate
        if (ScientificDocument::where('origin_domain', $originDomain)->where('origin_id', $originId)->exists()) {
            return;
        }

        $remoteUser = null;

        if (! empty($authorData['username']) && ! empty($authorData['domain'])) {
            $remoteUser = RemoteUser::firstOrCreate(
                [
                    'username' => $authorData['username'],
                    'domain' => $authorData['domain'],
                ],
                [
                    'display_name' => $authorData['display_name'] ?? $authorData['username'],
                    'institution' => $authorData['institution'] ?? null,
                ],
            );
        }

        ScientificDocument::create([
            'title' => $data['title'] ?? 'Sin titulo',
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'paper',
            'url' => $data['download_url'] ?? null,
            'doi' => $data['doi'] ?? null,
            'file_name' => $data['file_name'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'mime_type' => $data['mime_type'] ?? null,
            'remote_user_id' => $remoteUser?->id,
            'origin_domain' => $originDomain,
            'origin_id' => $originId,
            'is_shared' => true,
        ]);
    }
}
