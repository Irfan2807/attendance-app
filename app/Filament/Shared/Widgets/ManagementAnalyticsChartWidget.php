<?php

namespace App\Filament\Shared\Widgets;

use App\Filament\Concerns\HasManagementAnalyticsAccess;
use Closure;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

abstract class ManagementAnalyticsChartWidget extends ChartWidget
{
    use HasManagementAnalyticsAccess;

    protected function rememberAnalytics(string $suffix, int $seconds, Closure $callback): mixed
    {
        return Cache::remember($this->analyticsCacheKey($suffix), $seconds, $callback);
    }
}
