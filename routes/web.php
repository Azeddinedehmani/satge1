<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PharmacistController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ROUTE RACINE CORRIGÉE
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
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
Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('password.send.code');
Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// Protected routes
Route::middleware('auth')->group(function () {
    
    // Dashboard routes
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard')->middleware('admin');
    Route::get('/pharmacist/dashboard', [PharmacistController::class, 'index'])->name('pharmacist.dashboard')->middleware('pharmacist');
    
    // Inventory management routes (all authenticated users)
    Route::resource('inventory', ProductController::class)->names([
        'index' => 'inventory.index',
        'create' => 'inventory.create',
        'store' => 'inventory.store',
        'show' => 'inventory.show',
        'edit' => 'inventory.edit',
        'update' => 'inventory.update',
        'destroy' => 'inventory.destroy'
    ]);
    
    // Client management routes
    Route::resource('clients', ClientController::class);
    
    // Sales management routes
    Route::resource('sales', SaleController::class);
    Route::get('sales/{id}/print', [SaleController::class, 'print'])->name('sales.print');
    Route::get('sales/product/{id}', [SaleController::class, 'getProduct'])->name('sales.get-product');
    
    // Prescription management routes
    Route::resource('prescriptions', PrescriptionController::class);
    Route::get('prescriptions/{id}/deliver', [PrescriptionController::class, 'deliver'])->name('prescriptions.deliver');
    Route::post('prescriptions/{id}/deliver', [PrescriptionController::class, 'processDelivery'])->name('prescriptions.process-delivery');
    Route::get('prescriptions/{id}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');
    
    // Supplier management routes (all authenticated users can view, only admins can modify)
    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('suppliers/{id}', [SupplierController::class, 'show'])->name('suppliers.show');
    
    // Admin-only supplier management routes
    Route::middleware('admin')->group(function () {
        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
    
    // Purchase management routes (admin only)
    Route::middleware('admin')->group(function () {
        Route::resource('purchases', PurchaseController::class)->names([
            'index' => 'purchases.index',
            'create' => 'purchases.create',
            'store' => 'purchases.store',
            'show' => 'purchases.show',
            'edit' => 'purchases.edit',
            'update' => 'purchases.update',
            'destroy' => 'purchases.destroy'
        ]);
        
        // Additional purchase routes
        Route::get('purchases/{id}/print', [PurchaseController::class, 'print'])->name('purchases.print');
        Route::get('purchases/{id}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
        Route::post('purchases/{id}/receive', [PurchaseController::class, 'processReception'])->name('purchases.process-reception');
        Route::patch('purchases/{id}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    });
});