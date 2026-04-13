<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


class Programs extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'classes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name','description','school_id'];

    protected static function booted(): void
    {
        static::creating(function (self $classes): void {
            if (! $classes->id) {
                $classes->id = 'CLASS-'.Str::upper(Str::random(12));
            }
        });
    }
}
