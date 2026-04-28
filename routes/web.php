<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceExportController;
use App\Http\Controllers\AuthController;

// --- UNIFIED LOGIN ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- PUBLIC PAGES (Accessible by everyone) ---
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/services', function () {
    return view('services');
})->name('services');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');


// --- STAFF PAGES (Protected) ---
// Only logged-in users can see these pages.
Route::middleware(['auth'])->group(function () {
    
    // Attendance endpoints
    Route::get('/attendance/export', [AttendanceExportController::class, 'exportCsv'])->name('attendance.export');
    Route::get('/attendance/print', [AttendanceExportController::class, 'printView'])->name('attendance.print');

});

// --- SECRET LOGIN REDIRECT ---
// If someone tries to access a protected page but isn't logged in,
// Laravel usually sends them to 'login'. 
// We can override this behavior in 'app/bootstrap/app.php' OR
// rely on the Filament redirect.
//
// Currently, Filament handles the 'login' route name automatically 
// pointing to your secret path.