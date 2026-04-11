<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class School extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'school';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name', 'email', 'address', 'contact'];

    protected static function booted(): void
    {
        static::creating(function (self $school): void {
            if (! $school->id) {
                $school->id = 'UNI-'.Str::upper(Str::random(12));
            }
        });
    }
}
