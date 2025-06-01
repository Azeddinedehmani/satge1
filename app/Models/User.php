<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'phone',
    'date_of_birth',
    'address',
    'profile_photo',
    'is_active',
    'last_login_at',
    'last_login_ip',
    'permissions',
    'password_changed_at',
    'force_password_change',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
   protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'date_of_birth' => 'date',
    'is_active' => 'boolean',
    'last_login_at' => 'datetime',
    'permissions' => 'array',
    'password_changed_at' => 'datetime',
    'force_password_change' => 'boolean',
];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'responsable';
    }

    /**
     * Check if user is pharmacist
     *
     * @return bool
     */
    public function isPharmacist()
    {
        return $this->role === 'pharmacien';
    }
    public function activityLogs()
{
    return $this->hasMany(ActivityLog::class);
}
    
}