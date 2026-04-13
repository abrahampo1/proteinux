<?php

namespace App\Jobs\Federation;

use App\Models\Federation\FederationActivity;
use App\Models\Federation\FederationKeypair;
use App\Services\Federation\HttpSignatureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliverActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public FederationActivity $activity,
    ) {}

    public function handle(HttpSignatureService $signatureService): void
    {
        $this->activity->increment('attempts');

        $targetDomain = $this->activity->target_domain;
        $inboxUrl = "https://{$targetDomain}/federation/inbox";
        $body = json_encode($this->activity->payload);

        try {
            $keypair = FederationKeypair::getOrCreate();
            $headers = $signatureService->sign($inboxUrl, $body, $keypair->decryptedPrivateKey());

            $response = Http::withHeaders($headers)
                ->withBody($body, 'application/json')
                ->timeout(30)
                ->post($inboxUrl);

            if ($response->successful()) {
                $this->activity->markAsDelivered();

                Log::info("Federation activity delivered to {$targetDomain}", [
                    'activity_id' => $this->activity->id,
                ]);
            } else {
                $this->activity->markAsFailed("HTTP {$response->status()}: {$response->body()}");

                Log::warning("Federation delivery failed to {$targetDomain}", [
                    'activity_id' => $this->activity->id,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $e) {
            $this->activity->markAsFailed($e->getMessage());

            Log::error("Federation delivery exception for {$targetDomain}: {$e->getMessage()}", [
                'activity_id' => $this->activity->id,
            ]);

            throw $e;
        }
    }
}
