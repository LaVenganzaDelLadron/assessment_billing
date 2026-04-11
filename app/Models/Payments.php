<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class Payments extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'payments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['billing_id','amount','method','payment_date'];

    protected static function booted(): void
    {
        static::creating(function (self $payments): void {
            if (! $payments->id) {
                $payments->id = 'PAY-'.Str::upper(Str::random(12));
            }
        });
    }
}
