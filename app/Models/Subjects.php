<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Subjects extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'subjects';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['subject_code', 'subject_name', 'units', 'type', 'status'];

    protected static function booted(): void
    {
        static::creating(function (self $subject): void {
            if (! $subject->id) {
                $subject->id = 'SUBJ-'.Str::upper(Str::random(8));
            }
        });
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Programs::class, 'program_subject', 'subject_id', 'program_id')
            ->withPivot(['year_level', 'semester', 'school_year', 'status'])
            ->withTimestamps();
    }
}
