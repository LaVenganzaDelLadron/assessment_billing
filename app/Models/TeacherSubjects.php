<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;


class TeacherSubjects extends Model
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'teacher_subjects';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['teacher_id','subject_id'];

    protected static function booted(): void
    {
        static::creating(function (self $teacher_subjects): void {
            if (! $teacher_subjects->id) {
                $teacher_subjects->id = 'TSUBJ-'.Str::upper(Str::random(12));
            }
        });
    }
}
