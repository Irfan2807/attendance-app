<?php

namespace App\Filament\Shared\Widgets;

use App\Filament\Concerns\HasManagementAnalyticsAccess;
use Closure;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Cache;

abstract class ManagementAnalyticsStatsWidget extends StatsOverviewWidget
{
    use HasManagementAnalyticsAccess;

    protected function rememberAnalytics(string $suffix, int $seconds, Closure $callback): mixed
    {
        return Cache::remember($this->analyticsCacheKey($suffix), $seconds, $callback);
    }
}
