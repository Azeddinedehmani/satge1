<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prescription_number', 'client_id', 'doctor_name', 'doctor_phone', 'doctor_speciality',
        'prescription_date', 'expiry_date', 'status', 'medical_notes', 'pharmacist_notes',
        'created_by', 'delivered_by', 'delivered_at',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'expiry_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($prescription) {
            if (!$prescription->prescription_number) {
                $prescription->prescription_number = 'ORD-' . date('Ymd') . '-' . str_pad(
                    Prescription::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT
                );
            }
        });
    }

    public function client() { return $this->belongsTo(Client::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function deliveredBy() { return $this->belongsTo(User::class, 'delivered_by'); }
    public function prescriptionItems() { return $this->hasMany(PrescriptionItem::class); }

    public function isExpired() { return $this->expiry_date->isPast(); }
    public function isAboutToExpire($days = 7) { return $this->expiry_date->diffInDays(now()) <= $days && !$this->isExpired(); }

    public function updateStatus()
    {
        if ($this->isExpired()) {
            $this->status = 'expired';
        } elseif ($this->isFullyDelivered()) {
            $this->status = 'completed';
            if (!$this->delivered_at) {
                $this->delivered_at = now();
                $this->delivered_by = auth()->id();
            }
        } elseif ($this->isPartiallyDelivered()) {
            $this->status = 'partially_delivered';
        } else {
            $this->status = 'pending';
        }
        $this->save();
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'partially_delivered' => 'bg-info text-white',
            'completed' => 'bg-success',
            'expired' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'partially_delivered' => 'Partiellement délivrée',
            'completed' => 'Complètement délivrée',
            'expired' => 'Expirée',
            default => 'Inconnu'
        };
    }

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeActive($query) { return $query->where('expiry_date', '>=', now()); }
    public function scopeExpired($query) { return $query->where('expiry_date', '<', now()); }
}