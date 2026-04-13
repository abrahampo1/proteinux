<?php

namespace App\Services\Federation\Handlers;

use App\Models\Federation\FederationInstance;
use App\Models\Federation\RemoteUser;
use App\Models\Forum\ForumThread;
use App\Models\PredictedJob;

class ForumThreadHandler
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
        if (ForumThread::where('origin_domain', $originDomain)->where('origin_id', $originId)->exists()) {
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

        $predictedJobId = null;

        if (! empty($data['protein_reference'])) {
            $predictedJob = PredictedJob::where('protein_name', $data['protein_reference'])
                ->orWhere('uniprot_id', $data['protein_reference'])
                ->orWhere('pdb_id', $data['protein_reference'])
                ->first();

            $predictedJobId = $predictedJob?->id;
        }

        ForumThread::create([
            'title' => $data['title'] ?? 'Sin titulo',
            'body' => $data['body'] ?? '',
            'remote_user_id' => $remoteUser->id,
            'predicted_job_id' => $predictedJobId,
            'protein_reference' => $data['protein_reference'] ?? null,
            'origin_domain' => $originDomain,
            'origin_id' => $originId,
        ]);
    }
}
