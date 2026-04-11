<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class Submissions extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = ['assignment_id','student_id','content','status'];

    protected static function booted(): void
    {
        static::creating(function (self $submissions): void {
            if (! $submissions->id) {
                $submissions->id = 'SUBM-'.Str::upper(Str::random(12));
            }
        });
    }
}
