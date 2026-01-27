<?php

namespace App\Models;

// Add these Filament imports
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Implement the FilamentUser contract
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

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
        'phone_verified_at' => 'datetime',
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

    // The Gatekeeper Logic
    public function canAccessPanel(Panel $panel): bool
    {
        // 1 = Admin, 2 = Manager, 3 = Staff

        // 1. ADMIN PANEL (Orange)
        // ONLY Role 1 (Super Admin) allowed.
        if ($panel->getId() === 'admin') {
            return $this->role === 1;
        }

        // 2. STAFF PANEL (Green)
        // ONLY Role 2 (Manager) and Role 3 (Staff) allowed.
        // Admins (Role 1) are explicitly BLOCKED here.
        if ($panel->getId() === 'staff') {
            return in_array($this->role, [2, 3]);
        }

        return false; // Default: Block access to unknown panels
    }
}