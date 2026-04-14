<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class UserRole extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user_role';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['user_id', 'role_id'];

    protected static function booted(): void
    {
        static::creating(function (self $user_role): void {
            if (! $user_role->id) {
                $user_role->id = 'USERROLE-'.Str::upper(Str::random(12));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
