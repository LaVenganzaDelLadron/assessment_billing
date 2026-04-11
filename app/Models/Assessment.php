<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Assessment extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentFactory> */
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'assessments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'total_units',
        'tuition_fee',
        'miscellaneous_fee',
        'discount',
        'total_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_units' => 'float',
            'tuition_fee' => 'float',
            'miscellaneous_fee' => 'float',
            'discount' => 'float',
            'total_amount' => 'float',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $assessment): void {
            if (! $assessment->id) {
                $assessment->id = 'ASSESS-'.Str::upper(Str::random(12));
            }
        });
    }
}
