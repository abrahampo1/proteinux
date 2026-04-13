<?php

namespace App\Services\Federation;

use App\Models\Federation\FederationKeypair;

class FederationService
{
    /**
     * Check whether federation is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) config('services.federation.enabled');
    }

    /**
     * Return the local federation domain.
     */
    public function localDomain(): string
    {
        return (string) config('services.federation.domain');
    }

    /**
     * Return local instance information for federation handshakes.
     *
     * @return array{domain: string, name: string, description: string, public_key: string, proteinux_version: string}
     */
    public function localInstanceInfo(): array
    {
        $keypair = $this->getKeypair();

        return [
            'domain' => $this->localDomain(),
            'name' => (string) config('services.federation.instance_name'),
            'description' => (string) config('services.federation.instance_description'),
            'public_key' => $keypair->public_key,
            'proteinux_version' => '1.0',
        ];
    }

    /**
     * Get or create the local keypair.
     */
    public function getKeypair(): FederationKeypair
    {
        return FederationKeypair::getOrCreate();
    }
}
