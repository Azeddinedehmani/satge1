<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'user_id',
        'sale_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'has_prescription',
        'prescription_number',
        'notes',
        'sale_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'has_prescription' => 'boolean',
        'sale_date' => 'datetime',
    ];

    /**
     * Boot method to generate sale number.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($sale) {
            if (!$sale->sale_number) {
                // Generate a unique sale number
                $todaySalesCount = Sale::whereDate('created_at', today())->count();
                $sale->sale_number = 'VTE-' . date('Ymd') . '-' . str_pad($todaySalesCount + 1, 4, '0', STR_PAD_LEFT);
            }
            
            if (!$sale->sale_date) {
                $sale->sale_date = now();
            }
        });
    }

    /**
     * Get the client that owns the sale.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user (pharmacist) that made the sale.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale items for the sale.
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the products sold in this sale.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'sale_items')
                    ->withPivot('quantity', 'unit_price', 'total_price');
    }

    /**
     * Calculate and update totals.
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->saleItems()->sum('total_price');
        $this->tax_amount = $this->subtotal * 0.20; // 20% tax
        $this->total_amount = $this->subtotal + $this->tax_amount - ($this->discount_amount ?? 0);
        $this->save();
    }

    /**
     * Get payment status badge class.
     */
    public function getPaymentStatusBadgeAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'bg-success',
            'pending' => 'bg-warning text-dark',
            'failed' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Espèces',
            'card' => 'Carte bancaire',
            'insurance' => 'Assurance',
            'other' => 'Autre',
            default => ucfirst($this->payment_method)
        };
    }

    /**
     * Get payment status label.
     */
    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'Payé',
            'pending' => 'En attente',
            'failed' => 'Échoué',
            default => ucfirst($this->payment_status)
        };
    }

    /**
     * Check if sale has prescription products.
     */
    public function hasPrescriptionProducts()
    {
        return $this->saleItems()
                   ->whereHas('product', function($query) {
                       $query->where('prescription_required', true);
                   })->exists();
    }

    /**
     * Get total items count.
     */
    public function getTotalItemsAttribute()
    {
        return $this->saleItems()->sum('quantity');
    }

    /**
     * Scope for today's sales.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    /**
     * Scope for sales with prescription.
     */
    public function scopeWithPrescription($query)
    {
        return $query->where('has_prescription', true);
    }

    /**
     * Scope for paid sales.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}