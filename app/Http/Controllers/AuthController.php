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
        // Validate input - can be either email or phone
        $input = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        // Determine if input is phone or email
        $isPhone = preg_match('/^01[0-9]{8,9}$/', $input['login']);
        $isEmail = filter_var($input['login'], FILTER_VALIDATE_EMAIL);

        // Try to authenticate with phone or email
        if ($isPhone) {
            $credentials = ['phone' => $input['login'], 'password' => $input['password']];
        } elseif ($isEmail) {
            // For backward compatibility with existing email data
            $credentials = ['phone' => $input['login'], 'password' => $input['password']];
        } else {
            throw ValidationException::withMessages([
                'login' => ['Please enter a valid phone number or email address.'],
            ]);
        }

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
