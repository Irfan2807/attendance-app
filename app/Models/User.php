<?php

namespace App\Models;

// Add these Filament imports
use App\Enums\Role;
use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public const ROLE_HR = 4;

    /** @var array<string> */
    protected $fillable = [
        'name',
        'phone',
        'password',
        'role',
        'manager_id',
        'is_active',
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
        'role' => Role::class,
        'manager_id' => 'integer',
        'is_active' => 'boolean',
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

    public function isHr(): bool
    {
        return $this->role === Role::HR || $this->roleValue() === 4;
    }

    public function isManagerOrAdmin(): bool
    {
        return $this->isAdmin() || $this->isManager() || $this->isHr();
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
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

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function isOnApprovedLeave(Carbon|string $date): bool
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return $this->leaveRequests()
            ->where('status', \App\Enums\LeaveStatus::Approved->value)
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->exists();
    }

    // The Gatekeeper Logic
    public function canAccessPanel(Panel $panel): bool
    {
        // 1. ADMIN PANEL (Orange)
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        // 2. STAFF PANEL (Green)
        // Staff, Managers, HR Executives. Admins allowed for management oversight.
        if ($panel->getId() === 'staff') {
            return $this->isStaff() || $this->isManager() || $this->isHr() || $this->isAdmin();
        }

        return false; // Default: Block access to unknown panels
    }
}
