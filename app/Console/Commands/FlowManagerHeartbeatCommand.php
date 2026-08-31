<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FlowManagerHeartbeatCommand extends Command
{
    protected $signature = 'flowmanager:heartbeat';
    protected $description = 'Record the FlowManager scheduler heartbeat.';

    public function handle(): int
    {
        Cache::forever('flowmanager.scheduler_heartbeat', now()->toIso8601String());
        $this->info('Scheduler heartbeat recorded.');

        return self::SUCCESS;
    }
}
