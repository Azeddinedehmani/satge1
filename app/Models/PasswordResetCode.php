<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a new reset code for the given email
     */
    public static function generateCode(string $email): string
    {
        // Invalider tous les codes précédents pour cet email
        self::where('email', $email)->update(['used' => true]);

        // Générer un nouveau code à 6 chiffres
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Créer le nouveau code (valide pendant 10 minutes)
        self::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(10),
            'used' => false
        ]);

        return $code;
    }

    /**
     * Verify if the code is valid
     */
    public static function verifyCode(string $email, string $code): bool
    {
        $resetCode = self::where('email', $email)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($resetCode) {
            // Marquer le code comme utilisé
            $resetCode->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Clean expired codes
     */
    public static function cleanExpired(): void
    {
        self::where('expires_at', '<', Carbon::now())
            ->orWhere('used', true)
            ->delete();
    }
}