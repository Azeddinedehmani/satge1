<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PharmacistController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\SupplierController; // AJOUT

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
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
})->name('home');

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
    Route::resource('clients', ClientController::class)->names([
        'index' => 'clients.index',
        'create' => 'clients.create',
        'store' => 'clients.store',
        'show' => 'clients.show',
        'edit' => 'clients.edit', 
        'update' => 'clients.update',
        'destroy' => 'clients.destroy'
    ]);

    // Sales management routes
    Route::resource('sales', SaleController::class)->names([
        'index' => 'sales.index',
        'create' => 'sales.create',
        'store' => 'sales.store',
        'show' => 'sales.show',
        'edit' => 'sales.edit',
        'update' => 'sales.update',
        'destroy' => 'sales.destroy'
    ]);
    
    // Additional sales routes
    Route::get('sales/{id}/print', [SaleController::class, 'print'])->name('sales.print');
    
    // API routes for AJAX calls
    Route::prefix('api')->group(function () {
        Route::get('products/{id}', [SaleController::class, 'getProduct'])->name('api.products.show');
    });

    // Prescription management routes
    Route::resource('prescriptions', PrescriptionController::class)->names([
        'index' => 'prescriptions.index',
        'create' => 'prescriptions.create', 
        'store' => 'prescriptions.store',
        'show' => 'prescriptions.show',
        'edit' => 'prescriptions.edit',
        'update' => 'prescriptions.update',
        'destroy' => 'prescriptions.destroy'
    ]);
    
    // Additional prescription routes
    Route::get('prescriptions/{id}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');
    Route::get('prescriptions/{id}/deliver', [PrescriptionController::class, 'deliver'])->name('prescriptions.deliver');
    Route::post('prescriptions/{id}/deliver', [PrescriptionController::class, 'processDelivery'])->name('prescriptions.process-delivery');

    // NOUVEAU : Supplier management routes (accessible by both admin and pharmacist, but admin only for CUD operations)
    Route::resource('suppliers', SupplierController::class)->names([
        'index' => 'suppliers.index',
        'create' => 'suppliers.create',
        'store' => 'suppliers.store',
        'show' => 'suppliers.show',
        'edit' => 'suppliers.edit',
        'update' => 'suppliers.update',
        'destroy' => 'suppliers.destroy'
    ]);

    // Development and debugging routes (remove in production)
    if (config('app.debug')) {
        // Test route for sales system
        Route::get('/test-sale', function() {
            $products = \App\Models\Product::where('stock_quantity', '>', 0)->take(5)->get();
            $clients = \App\Models\Client::active()->take(5)->get();
            
            return response()->json([
                'status' => 'success',
                'timestamp' => now()->toDateTimeString(),
                'user' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name,
                    'role' => auth()->user()->role
                ],
                'data' => [
                    'products_count' => $products->count(),
                    'clients_count' => $clients->count(),
                    'products' => $products->map(function($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'dosage' => $p->dosage,
                            'stock' => $p->stock_quantity,
                            'price' => $p->selling_price,
                            'prescription_required' => $p->prescription_required
                        ];
                    }),
                    'clients' => $clients->map(function($c) {
                        return [
                            'id' => $c->id,
                            'name' => $c->full_name,
                            'active' => $c->active,
                            'has_allergies' => !empty($c->allergies)
                        ];
                    })
                ]
            ]);
        })->name('test.sale');

        // Test route for database status
        Route::get('/test-db', function() {
            try {
                $tables = [
                    'users' => \App\Models\User::count(),
                    'products' => \App\Models\Product::count(),
                    'clients' => \App\Models\Client::count(),
                    'sales' => \App\Models\Sale::count(),
                    'sale_items' => \App\Models\SaleItem::count(),
                    'prescriptions' => \Illuminate\Support\Facades\Schema::hasTable('prescriptions') 
                        ? \App\Models\Prescription::count() : 'Table not found',
                    'categories' => \App\Models\Category::count(),
                    'suppliers' => \App\Models\Supplier::count(),
                ];

                return response()->json([
                    'status' => 'success',
                    'database' => config('database.default'),
                    'tables' => $tables,
                    'latest_sale' => \App\Models\Sale::latest()->first(),
                    'products_with_stock' => \App\Models\Product::where('stock_quantity', '>', 0)->count(),
                    'active_clients' => \App\Models\Client::where('active', true)->count(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }
        })->name('test.db');
    }
});

// Fallback route for undefined routes
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'pharmacist.dashboard')
            ->with('warning', 'Page non trouvée. Redirection vers le tableau de bord.');
    }
    return redirect()->route('login')->with('error', 'Page non trouvée.');
});