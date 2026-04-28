<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // Redirect if already authenticated
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $input = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        // Only phone number login is supported
        if (!preg_match('/^01[0-9]{8,9}$/', $input['login'])) {
            throw ValidationException::withMessages([
                'login' => ['Please enter a valid Malaysian phone number (e.g. 0123456789).'],
            ]);
        }

        $credentials = ['phone' => $input['login'], 'password' => $input['password']];

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole();
        }

        throw ValidationException::withMessages([
            'login' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }
        // staff or manager
        if (in_array($user->role, [3, 2])) {
            return redirect('/staff');
        }

        // master admin
        return redirect('/admin');
    }
}
