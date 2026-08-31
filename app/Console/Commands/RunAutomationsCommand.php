<?php

namespace App\Console\Commands;

use App\Services\AutomationEngine;
use Illuminate\Console\Command;

class RunAutomationsCommand extends Command
{
    protected $signature = 'flowmanager:automations';
    protected $description = 'Run active FlowManager automation rules.';

    public function handle(AutomationEngine $engine): int
    {
        $summary = $engine->run();
        $this->info(sprintf(
            'Rules: %d, executed: %d, skipped: %d, failed: %d',
            $summary['rules'],
            $summary['executed'],
            $summary['skipped'],
            $summary['failed'],
        ));

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
