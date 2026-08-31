<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('flowmanager:heartbeat')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('flowmanager:reminders')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('flowmanager:automations')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('flowmanager:backup --verify')
    ->dailyAt('02:15')
    ->withoutOverlapping();

Schedule::command('flowmanager:scheduled-reports')->hourly()->withoutOverlapping();
