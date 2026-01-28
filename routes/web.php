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

// Redirect root to admin login
Route::redirect('/', '/admin/login');

// Wallet Pass Download
Route::get('/pass/download/{id}', [PassController::class, 'download'])->name('pass.download')->where('id', '[0-9]+');

// Google Wallet Redirect
Route::get('/google-wallet/redirect', [PassController::class, 'downloadGooglePass'])->name('google.wallet.redirect');

// Public Pass View
Route::get('/v/{unique_member_id}', [PassController::class, 'publicView'])->name('pass.public_view');



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
    Route::get('/members/store', fn() => redirect()->route('admin.members.create'));
    Route::post('/members/store', [AdminMemberController::class, 'store'])->name('members.store');
    Route::get('/members/{id}', [AdminMemberController::class, 'show'])->name('members.show')->where('id', '[0-9]+');
    Route::post('/members/update-pass-status', [AdminMemberController::class, 'updatePassStatus'])->name('members.updatePassStatus');
    Route::post('/members/regenerate-pass', [AdminMemberController::class, 'regeneratePass'])->name('members.regenerate');
    Route::post('/members/delete', [AdminMemberController::class, 'destroy'])->name('members.delete');
    // Pass Gallery
    Route::get('/passes', [\App\Http\Controllers\Admin\PassController::class, 'index'])->name('passes.index');
});
