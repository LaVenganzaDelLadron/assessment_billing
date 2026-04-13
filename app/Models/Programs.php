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

    protected $table = 'programs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['code','name','department','status','school_id'];

    protected static function booted(): void
    {
        static::creating(function (self $programs): void {
            if (! $programs->id) {
                $programs->id = 'PROG-'.Str::upper(Str::random(12));
            }
        });
    }
}
