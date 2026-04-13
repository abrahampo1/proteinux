<?php

namespace App\Console\Commands;

use App\Models\Federation\FederationInstance;
use Illuminate\Console\Command;

class FederationStatusCommand extends Command
{
    protected $signature = 'federation:status';

    protected $description = 'List all federation peers and their status';

    public function handle(): int
    {
        $instances = FederationInstance::orderBy('domain')->get();

        if ($instances->isEmpty()) {
            $this->info('No federation peers configured.');

            return self::SUCCESS;
        }

        $this->table(
            ['Domain', 'Name', 'Status', 'Last Seen', 'Last Synced'],
            $instances->map(fn (FederationInstance $instance) => [
                $instance->domain,
                $instance->name,
                $instance->status,
                $instance->last_seen_at?->diffForHumans() ?? 'never',
                $instance->last_synced_at?->diffForHumans() ?? 'never',
            ]),
        );

        $this->newLine();
        $this->info("Total: {$instances->count()} peer(s) — Active: {$instances->where('status', 'active')->count()}");

        return self::SUCCESS;
    }
}
