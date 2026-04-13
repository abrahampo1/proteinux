<?php

namespace App\Services\Federation\Handlers;

use App\Models\Federation\FederationInstance;
use App\Models\Federation\RemoteUser;
use App\Models\Forum\ForumThread;
use App\Models\PredictedJob;

class ForumThreadHandler
{
    /**
     * Handle an inbound federated forum thread activity.
     *
     * @param  array{author: array{username: string, domain: string, display_name?: string}, title: string, body: string, origin_id: string, origin_domain: string, protein_reference?: string}  $payload
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

        $predictedJobId = null;

        if (! empty($payload['protein_reference'])) {
            $predictedJob = PredictedJob::where('protein_name', $payload['protein_reference'])
                ->orWhere('uniprot_id', $payload['protein_reference'])
                ->orWhere('pdb_id', $payload['protein_reference'])
                ->first();

            $predictedJobId = $predictedJob?->id;
        }

        ForumThread::firstOrCreate(
            [
                'origin_domain' => $payload['origin_domain'],
                'origin_id' => $payload['origin_id'],
            ],
            [
                'title' => $payload['title'],
                'body' => $payload['body'],
                'remote_user_id' => $remoteUser->id,
                'predicted_job_id' => $predictedJobId,
                'protein_reference' => $payload['protein_reference'] ?? null,
            ],
        );
    }
}
