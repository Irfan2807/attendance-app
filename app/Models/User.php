<?php

namespace App\Models;

// Add these Filament imports
use App\Enums\Role;
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
        'role' => Role::class,
    ];

    /**
     * Get the column name for the "username" used for authentication.
     * This allows login with phone number instead of email.
     */
    public function getAuthIdentifierName(): string
    {
        return 'phone';
    }

    public function roleValue(): int
    {
        return $this->role instanceof Role ? $this->role->value : (int) $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::SuperAdmin || $this->roleValue() === 1;
    }

    public function isManager(): bool
    {
        return $this->role === Role::Manager || $this->roleValue() === 2;
    }

    public function isStaff(): bool
    {
        return $this->role === Role::Staff || $this->roleValue() === 3;
    }

    public function isManagerOrAdmin(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function dashboardPath(): string
    {
        return $this->isAdmin() ? '/admin' : '/staff';
    }

    public function mileageLogs(): HasMany
    {
        return $this->hasMany(MileageLog::class);
    }

    public function infractions(): HasMany
    {
        return $this->hasMany(AttendanceInfraction::class);
    }

    // The Gatekeeper Logic
    public function canAccessPanel(Panel $panel): bool
    {
        // 1. ADMIN PANEL (Orange)
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        // 2. STAFF PANEL (Green)
        // Staff and Managers. Admins allowed for management oversight.
        if ($panel->getId() === 'staff') {
            return $this->isStaff() || $this->isManager() || $this->isAdmin();
        }

        return false; // Default: Block access to unknown panels
    }
}
