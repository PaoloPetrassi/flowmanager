<?php

use App\Jobs\QueueHeartbeatJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('flowmanager:heartbeat')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::job(new QueueHeartbeatJob)
    ->everyMinute();

Schedule::command('flowmanager:reminders')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('flowmanager:automations')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('flowmanager:queue-backup')
    ->dailyAt('02:15')
    ->withoutOverlapping();

Schedule::command('flowmanager:scheduled-reports')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('queue:prune-batches --hours=48 --unfinished=72 --cancelled=72')
    ->dailyAt('03:00');

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('03:10');

Schedule::command('flowmanager:prune-jobs')
    ->dailyAt('03:20')
    ->withoutOverlapping();
