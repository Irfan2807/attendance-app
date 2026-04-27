<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        // Get the authenticated user
        $user = Auth::user();

        // Redirect based on user role
        if ($user && $user->role === 1) {
            // Admin (1) -> Admin Panel
            return redirect()->intended('/admin');
        } else {
            // Manager (2) or Staff (3) -> Staff Panel
            return redirect()->intended('/staff');
        }
    }
}