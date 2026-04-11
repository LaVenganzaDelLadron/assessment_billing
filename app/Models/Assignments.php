<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


class Assignments extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = ['subject_id','name','description','due_date'];

    protected static function booted(): void
    {
        static::creating(function (self $assignments): void {
            if (! $assignments->id) {
                $assignments->id = 'ASSIGN-'.Str::upper(Str::random(12));
            }
        });
    }
}
