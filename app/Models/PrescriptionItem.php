<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id', 'product_id', 'quantity_prescribed', 'quantity_delivered',
        'dosage_instructions', 'duration_days', 'instructions', 'is_substitutable',
    ];

    protected $casts = [
        'quantity_prescribed' => 'integer',
        'quantity_delivered' => 'integer',
        'duration_days' => 'integer',
        'is_substitutable' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($item) { $item->prescription->updateStatus(); });
        static::deleted(function ($item) { $item->prescription->updateStatus(); });
    }

    public function prescription() { return $this->belongsTo(Prescription::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public function getRemainingQuantityAttribute() { return $this->quantity_prescribed - $this->quantity_delivered; }
    public function isFullyDelivered() { return $this->quantity_delivered >= $this->quantity_prescribed; }
    public function isPartiallyDelivered() { return $this->quantity_delivered > 0 && $this->quantity_delivered < $this->quantity_prescribed; }
    public function getDeliveryProgressAttribute() { return $this->quantity_prescribed == 0 ? 0 : round(($this->quantity_delivered / $this->quantity_prescribed) * 100, 1); }
}