<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto clock-out safety net: enforce max 10 hours per shift and create warnings
Schedule::command('attendance:auto-clock-out')->everyTenMinutes();
