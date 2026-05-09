<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

trait HasManagementAnalyticsAccess
{
    public static function canView(): bool
    {
        $user = Auth::user();

        return $user && ($user->isAdmin() || $user->isManager());
    }

    protected function analyticsCacheKey(string $suffix): string
    {
        $panelId = Filament::getCurrentPanel()?->getId() ?? 'web';
        $userId = Auth::id() ?? 'guest';

        return sprintf('analytics:%s:%s:%s', $panelId, $userId, $suffix);
    }
}
