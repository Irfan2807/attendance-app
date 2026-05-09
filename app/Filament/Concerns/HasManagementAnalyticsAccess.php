<?php

namespace App\Filament\Concerns;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

trait HasManagementAnalyticsAccess
{
    public static function canView(): bool
    {
        $user = Auth::user();

        return $user && in_array((int) $user->role, [User::ROLE_ADMIN, User::ROLE_MANAGER], true);
    }

    protected function analyticsCacheKey(string $suffix): string
    {
        $panelId = Filament::getCurrentPanel()?->getId() ?? 'web';
        $userId = Auth::id() ?? 'guest';

        return sprintf('analytics:%s:%s:%s', $panelId, $userId, $suffix);
    }
}
