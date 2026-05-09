<?php

namespace App\Models;

// Add these Filament imports
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Implement the FilamentUser contract
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 1;

    public const ROLE_MANAGER = 2;

    public const ROLE_STAFF = 3;

    /** @var array<string> */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'role',
        'incomplete_clock_out_count',
    ];

    /** @var array<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'password' => 'hashed',
        'role' => 'integer',
    ];

    /**
     * Get the column name for the "username" used for authentication.
     * This allows login with phone number instead of email.
     */
    public function getAuthIdentifierName(): string
    {
        return 'phone';
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function dashboardPath(): string
    {
        return $this->isAdmin() ? '/admin' : '/staff';
    }

    // The Gatekeeper Logic
    public function canAccessPanel(Panel $panel): bool
    {
        // 1. ADMIN PANEL (Orange)
        // Only admins can access the admin panel.
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        // 2. STAFF PANEL (Green)
        // Admins remain admin-only and are explicitly blocked from this panel.
        if ($panel->getId() === 'staff') {
            return $this->isManager() || $this->isStaff();
        }

        return false; // Default: Block access to unknown panels
    }
}
