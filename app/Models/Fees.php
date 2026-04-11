<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


class Fees extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'fees';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name','amount'];

    protected static function booted(): void
    {
        static::creating(function (self $fees): void {
            if (! $fees->id) {
                $fees->id = 'FEE-'.Str::upper(Str::random(12));
            }
        });
    }
}
