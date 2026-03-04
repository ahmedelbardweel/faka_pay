<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'iban',
    ];

    protected static function booted()
    {
        static::creating(function ($wallet) {
            if (!$wallet->iban) {
                $wallet->iban = self::generateUniqueIban();
            }
        });
    }

    public static function generateUniqueIban()
    {
        $prefix = 'PS';
        $bankCode = 'FAKA';
        
        do {
            // PS + 2 check digits + FAKA + 21 random alphanumeric
            $checkDigits = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $randomPart = strtoupper(\Illuminate\Support\Str::random(21));
            $iban = $prefix . $checkDigits . $bankCode . $randomPart;
        } while (self::where('iban', $iban)->exists());

        return $iban;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
