<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PharmacistController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SaleController;

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
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password reset routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot');
    Route::post('/send-reset-code', [AuthController::class, 'sendResetCode'])->name('password.send.code');
    Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Admin routes
    Route::prefix('admin')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    });

    // Pharmacist routes
    Route::prefix('pharmacist')->middleware(\App\Http\Middleware\PharmacistMiddleware::class)->group(function () {
        Route::get('/dashboard', [PharmacistController::class, 'index'])->name('pharmacist.dashboard');
    });

    // Inventory management routes (accessible by both admin and pharmacist)
    Route::resource('inventory', ProductController::class);

    // Client management routes
    Route::resource('clients', ClientController::class);

    // Sales management routes
    Route::resource('sales', SaleController::class);
    Route::get('sales/{id}/print', [SaleController::class, 'print'])->name('sales.print');
    Route::get('api/products/{id}', [SaleController::class, 'getProduct'])->name('api.products.show');
    // Prescription management routes
Route::resource('prescriptions', PrescriptionController::class);
Route::get('prescriptions/{id}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');
Route::get('prescriptions/{id}/deliver', [PrescriptionController::class, 'deliver'])->name('prescriptions.deliver');
Route::post('prescriptions/{id}/deliver', [PrescriptionController::class, 'processDelivery'])->name('prescriptions.process-delivery');
});