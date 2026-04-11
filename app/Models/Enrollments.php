<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


class Enrollments extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'enrollments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['student_id','class_id','year_id'];

    protected static function booted(): void
    {
        static::creating(function (self $enrollments): void {
            if (! $enrollments->id) {
                $enrollments->id = 'ENR-'.Str::upper(Str::random(12));
            }
        });
    }

}
