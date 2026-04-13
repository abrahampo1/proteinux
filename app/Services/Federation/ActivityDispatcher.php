<?php

namespace App\Services\Federation;

use App\Jobs\Federation\DeliverActivity;
use App\Models\Federation\FederationActivity;
use App\Models\Federation\FederationInstance;

class ActivityDispatcher
{
    public function __construct(
        private FederationService $federationService,
    ) {}

    /**
     * Dispatch an activity to one or all active federation peers.
     *
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $type, string $action, array $payload, ?FederationInstance $target = null): void
    {
        $localDomain = $this->federationService->localDomain();

        $fullPayload = [
            'type' => $type,
            'action' => $action,
            'data' => $payload,
            'source_domain' => $localDomain,
            'timestamp' => now()->toIso8601String(),
        ];

        $targets = $target
            ? collect([$target])
            : FederationInstance::active()->get();

        foreach ($targets as $targetInstance) {
            $activity = FederationActivity::create([
                'direction' => 'outbound',
                'type' => $type,
                'payload' => $fullPayload,
                'source_domain' => $localDomain,
                'target_domain' => $targetInstance->domain,
                'status' => 'pending',
                'attempts' => 0,
            ]);

            DeliverActivity::dispatch($activity);
        }
    }
}
