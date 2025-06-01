<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that was affected
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Get action badge class
     */
    public function getActionBadgeAttribute()
    {
        return match($this->action) {
            'create' => 'bg-success',
            'update' => 'bg-warning text-dark',
            'delete' => 'bg-danger',
            'view' => 'bg-info',
            'login' => 'bg-primary',
            'logout' => 'bg-secondary',
            'export' => 'bg-dark',
            default => 'bg-light text-dark'
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'create' => 'fas fa-plus',
            'update' => 'fas fa-edit',
            'delete' => 'fas fa-trash',
            'view' => 'fas fa-eye',
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'export' => 'fas fa-download',
            default => 'fas fa-info'
        };
    }

    /**
     * Get formatted model name
     */
    public function getModelNameAttribute()
    {
        if (!$this->model_type) return 'Système';
        
        return match($this->model_type) {
            'App\Models\User' => 'Utilisateur',
            'App\Models\Product' => 'Produit',
            'App\Models\Sale' => 'Vente',
            'App\Models\Client' => 'Client',
            'App\Models\Prescription' => 'Ordonnance',
            'App\Models\Purchase' => 'Achat',
            'App\Models\Supplier' => 'Fournisseur',
            default => class_basename($this->model_type)
        };
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific action
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific model
     */
    public function scopeForModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Log activity helper
     */
    public static function logActivity($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        if (!auth()->check()) return;

        $log = new static();
        $log->user_id = auth()->id();
        $log->action = $action;
        $log->description = $description;
        $log->ip_address = request()->ip();
        $log->user_agent = request()->userAgent();

        if ($model) {
            $log->model_type = get_class($model);
            $log->model_id = $model->id ?? null;
        }

        if ($oldValues) {
            $log->old_values = $oldValues;
        }

        if ($newValues) {
            $log->new_values = $newValues;
        }

        $log->save();

        return $log;
    }
}