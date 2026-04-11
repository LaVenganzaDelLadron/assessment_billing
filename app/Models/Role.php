<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Role extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'role';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name'];

    protected static function booted(): void
    {
        static::creating(function (self $role): void {
            if (! $role->id) {
                $role->id = 'ROLE-'.Str::upper(Str::random(12));
            }
        });
    }
}
