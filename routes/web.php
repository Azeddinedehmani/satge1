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