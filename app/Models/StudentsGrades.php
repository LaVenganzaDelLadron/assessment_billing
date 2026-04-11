<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class StudentsGrades extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'students_grades';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['submission_id','grade','remarks'];


    protected static function booted(): void
    {
        static::creating(function (self $classes): void {
            if (! $classes->id) {
                $classes->id = 'GRADE-'.Str::upper(Str::random(12));
            }
        });
    }
}
