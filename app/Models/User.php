<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use BelongsToTenant, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'society_id', 'name', 'email', 'phone', 'password', 'avatar',
        'designation', 'gender', 'status', 'locale',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
        'last_login_at', 'last_login_ip', 'password_changed_at', 'email_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'         => 'datetime',
            'last_login_at'             => 'datetime',
            'password_changed_at'       => 'datetime',
            'two_factor_confirmed_at'   => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_secret'         => 'encrypted',
            'password'                  => 'hashed',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    public function getAvatarUrlAttribute(): string
    {
        // Read raw attributes so this works even when the model was loaded with
        // a partial column selection (strict mode throws on missing attributes).
        $avatar = $this->attributes['avatar'] ?? null;

        if ($avatar) {
            return Storage::url($avatar);
        }

        $name = $this->attributes['name'] ?? 'User';

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=0d6efd&color=fff';
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }
}
