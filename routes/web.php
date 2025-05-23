<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PharmacistController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\PharmacistMiddleware;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('pharmacist.dashboard');
        }
    }
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot');
Route::post('/send-reset-code', [AuthController::class, 'sendResetCode'])->name('password.send.code');
Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// Admin routes - Référence directe à la classe middleware
Route::prefix('admin')->middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// Pharmacist routes - Référence directe à la classe middleware
Route::prefix('pharmacist')->middleware(['auth', PharmacistMiddleware::class])->group(function () {
    Route::get('/dashboard', [PharmacistController::class, 'index'])->name('pharmacist.dashboard');
});

// Routes pour la gestion des produits
Route::middleware(['auth'])->group(function () {
    Route::resource('inventory', ProductController::class);
});