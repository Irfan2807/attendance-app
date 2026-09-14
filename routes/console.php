<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto clock-out safety net: enforce max shift hours and create warnings
Schedule::command('attendance:auto-clock-out')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Sync Malaysian public holidays monthly to ensure updated calendar
Schedule::command('holidays:sync')
    ->monthly()
    ->withoutOverlapping()
    ->runInBackground();

// Check fleet compliance daily (road tax & service due) and dispatch in-app alerts
Schedule::command('fleet:check-alerts')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();


