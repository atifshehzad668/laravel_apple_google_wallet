<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PassController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Member Registration
Route::get('/', [MemberController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [MemberController::class, 'register'])->name('register');

// Wallet Pass Download
Route::get('/pass/download/{id}', [PassController::class, 'download'])->name('pass.download');

// Google Wallet Redirect
Route::get('/google-wallet/redirect', [PassController::class, 'downloadGooglePass'])->name('google.wallet.redirect');
Route::get('/googlepass', [PassController::class, 'GooglePass'])->name('GooglePass');



/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Admin Panel
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Protected Admin Routes
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');

    // Members Management
    Route::get('/members', [AdminMemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [AdminMemberController::class, 'create'])->name('members.create');
    Route::post('/members/store', [AdminMemberController::class, 'store'])->name('members.store');
    Route::get('/members/{id}', [AdminMemberController::class, 'show'])->name('members.show');
    Route::post('/members/regenerate-pass', [AdminMemberController::class, 'regeneratePass'])->name('members.regenerate');
    Route::post('/members/delete', [AdminMemberController::class, 'destroy'])->name('members.delete');
});
