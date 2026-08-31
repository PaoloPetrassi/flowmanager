<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendRemindersCommand extends Command
{
    protected $signature = 'flowmanager:reminders';
    protected $description = 'Send due date and SLA reminders.';

    public function handle(ReminderService $reminders): int
    {
        $summary = $reminders->run();
        $this->info("Task reminders: {$summary['tasks']}; ticket reminders: {$summary['tickets']}");

        return self::SUCCESS;
    }
}
