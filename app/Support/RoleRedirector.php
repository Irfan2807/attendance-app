<?php

namespace App\Support;

use App\Models\User;

class RoleRedirector
{
    public static function pathFor(?User $user): string
    {
        if (! $user) {
            return '/login';
        }

        return $user->dashboardPath();
    }
}
