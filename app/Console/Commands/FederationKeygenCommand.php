<?php

namespace App\Console\Commands;

use App\Models\Federation\FederationKeypair;
use Illuminate\Console\Command;

class FederationKeygenCommand extends Command
{
    protected $signature = 'federation:keygen';

    protected $description = 'Generate or retrieve the federation RSA keypair';

    public function handle(): int
    {
        $this->info('Generating federation keypair...');

        $keypair = FederationKeypair::getOrCreate();

        $this->info('Federation keypair ready.');
        $this->newLine();
        $this->line('Public key:');
        $this->newLine();
        $this->line($keypair->public_key);

        return self::SUCCESS;
    }
}
