<?php

namespace App\Console\Commands;

use App\Models\Federation\FederationInstance;
use App\Services\Federation\PeerDiscoveryService;
use Illuminate\Console\Command;

class FederationSyncCommand extends Command
{
    protected $signature = 'federation:sync';

    protected $description = 'Fetch updates from all active federation peers';

    public function handle(PeerDiscoveryService $peerDiscoveryService): int
    {
        $peers = FederationInstance::active()->get();

        if ($peers->isEmpty()) {
            $this->info('No active federation peers found.');

            return self::SUCCESS;
        }

        $this->info("Syncing with {$peers->count()} active peer(s)...");

        foreach ($peers as $peer) {
            $this->line("  Fetching info from {$peer->domain}...");

            try {
                $info = $peerDiscoveryService->fetchInstanceInfo($peer->domain);

                $peer->update([
                    'name' => $info['name'] ?? $peer->name,
                    'description' => $info['description'] ?? $peer->description,
                    'public_key' => $info['public_key'] ?? $peer->public_key,
                    'metadata' => $info,
                    'last_seen_at' => now(),
                    'last_synced_at' => now(),
                ]);

                $this->info("  Synced: {$peer->domain}");
            } catch (\Throwable $e) {
                $this->error("  Failed: {$peer->domain} - {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Sync complete.');

        return self::SUCCESS;
    }
}
