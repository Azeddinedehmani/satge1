<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot method to calculate total price.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($saleItem) {
            $saleItem->total_price = $saleItem->quantity * $saleItem->unit_price;
        });

        static::saved(function ($saleItem) {
            // Update sale totals when sale item is saved
            $saleItem->sale->calculateTotals();
        });

        static::deleted(function ($saleItem) {
            // Update sale totals when sale item is deleted
            $saleItem->sale->calculateTotals();
        });
    }

    /**
     * Get the sale that owns the sale item.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product that owns the sale item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}