<?php

namespace App\Jobs\Federation;

use App\Models\Federation\FederationActivity;
use App\Services\Federation\ActivityProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessInboundActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public FederationActivity $activity,
    ) {}

    public function handle(ActivityProcessor $processor): void
    {
        $processor->process($this->activity);
    }
}
