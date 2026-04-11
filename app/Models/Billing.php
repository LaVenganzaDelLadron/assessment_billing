<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


class Billing extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'billing';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['student_id','fee_id','total_amount','status','billing_date','due_date'];

    protected static function booted(): void
    {
        static::creating(function (self $billing): void {
            if (! $billing->id) {
                $billing->id = 'BILL-'.Str::upper(Str::random(12));
            }
        });
    }
}
