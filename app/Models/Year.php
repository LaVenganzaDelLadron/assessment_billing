<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Year extends Model
{
use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'year_level';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['year'];

    protected static function booted(): void
    {
        static::creating(function (self $user_role): void {
            if (! $user_role->id) {
                $user_role->id = 'YEAR-'.Str::upper(Str::random(12));
            }
        });
    }
}
