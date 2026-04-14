<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Programs extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'programs';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['code', 'name', 'department', 'status'];

    protected static function booted(): void
    {
        static::creating(function (self $programs): void {
            if (! $programs->id) {
                $programs->id = 'PROG-'.Str::upper(Str::random(12));
            }
        });
    }

    public function students(): HasMany
    {
        return $this->hasMany(Students::class, 'program_id', 'id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subjects::class, 'program_subject', 'program_id', 'subject_id')
            ->withPivot(['year_level', 'semester', 'school_year', 'status'])
            ->withTimestamps();
    }
}
