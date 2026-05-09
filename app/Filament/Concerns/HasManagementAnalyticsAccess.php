<?php

namespace App\Filament\Concerns;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

trait HasManagementAnalyticsAccess
{
    public static function canView(): bool
    {
        $user = Auth::user();

        return $user instanceof User && ($user->isAdmin() || $user->isManager());
    }

    protected function analyticsCacheKey(string $suffix): string
    {
        $panelId = Filament::getCurrentPanel()?->getId() ?? 'web';
        $userId = Auth::id();

        if ($userId === null) {
            throw new RuntimeException('Analytics cache key requires an authenticated user.');
        }

        return sprintf('analytics:%s:%s:%s', $panelId, $userId, $suffix);
    }
}
