<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class Subjects extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'subjects';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['code','name','class_id'];

    protected static function booted(): void
    {
        static::creating(function (self $subjects): void {
            if (! $subjects->id) {
                $subjects->id = 'SUBJ-'.Str::upper(Str::random(12));
            }
        });
    }
}
