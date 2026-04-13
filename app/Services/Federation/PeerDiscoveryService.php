<?php

namespace App\Services\Federation;

use App\Models\Federation\FederationInstance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PeerDiscoveryService
{
    public function __construct(
        private FederationService $federationService,
        private HttpSignatureService $signatureService,
    ) {}

    /**
     * Request peering with a remote Proteinux instance.
     */
    public function requestPeering(string $remoteDomain): FederationInstance
    {
        $remoteInfo = $this->fetchInstanceInfo($remoteDomain);

        $instance = FederationInstance::updateOrCreate(
            ['domain' => $remoteDomain],
            [
                'name' => $remoteInfo['name'] ?? $remoteDomain,
                'description' => $remoteInfo['description'] ?? null,
                'public_key' => $remoteInfo['public_key'] ?? null,
                'status' => 'pending',
                'metadata' => $remoteInfo,
                'last_seen_at' => now(),
            ],
        );

        $localInfo = $this->federationService->localInstanceInfo();
        $body = json_encode([
            'type' => 'peering_request',
            'instance' => $localInfo,
        ]);

        $this->sendSignedRequest(
            "https://{$remoteDomain}/federation/peering/request",
            $body,
        );

        return $instance;
    }

    /**
     * Accept a peering request from a remote instance.
     */
    public function acceptPeering(FederationInstance $instance): void
    {
        $instance->update([
            'status' => 'active',
            'last_seen_at' => now(),
        ]);

        $localInfo = $this->federationService->localInstanceInfo();
        $body = json_encode([
            'type' => 'peering_accepted',
            'instance' => $localInfo,
        ]);

        $this->sendSignedRequest(
            "https://{$instance->domain}/federation/peering/accept",
            $body,
        );
    }

    /**
     * Reject a peering request from a remote instance.
     */
    public function rejectPeering(FederationInstance $instance): void
    {
        $instance->update(['status' => 'rejected']);

        $localInfo = $this->federationService->localInstanceInfo();
        $body = json_encode([
            'type' => 'peering_rejected',
            'instance' => $localInfo,
        ]);

        $this->sendSignedRequest(
            "https://{$instance->domain}/federation/peering/reject",
            $body,
        );
    }

    /**
     * Fetch instance info from a remote domain.
     *
     * @return array<string, mixed>
     */
    public function fetchInstanceInfo(string $domain): array
    {
        $response = Http::acceptJson()
            ->timeout(15)
            ->get("https://{$domain}/federation/instance-info");

        if ($response->failed()) {
            Log::warning("Failed to fetch instance info from {$domain}", [
                'status' => $response->status(),
            ]);

            return ['domain' => $domain, 'name' => $domain];
        }

        return $response->json();
    }

    /**
     * Send a signed POST request to a remote federation endpoint.
     */
    private function sendSignedRequest(string $url, string $body): void
    {
        try {
            $keypair = $this->federationService->getKeypair();
            $headers = $this->signatureService->sign($url, $body, $keypair->decryptedPrivateKey());

            Http::withHeaders($headers)
                ->withBody($body, 'application/json')
                ->timeout(15)
                ->post($url);
        } catch (\Throwable $e) {
            Log::warning("Federation signed request failed: {$e->getMessage()}", [
                'url' => $url,
            ]);
        }
    }
}
