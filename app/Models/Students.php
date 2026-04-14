<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Students extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'student';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name', 'program_id'];

    protected static function booted(): void
    {
        static::creating(function (self $student): void {
            if (! $student->id) {
                $student->id = 'STU-'.Str::upper(Str::random(8));
            }
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Programs::class, 'program_id', 'id');
    }
}
