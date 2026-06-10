<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $fillable = [
        'society_id', 'user_id', 'email', 'status', 'ip_address', 'user_agent',
        'device', 'platform', 'browser', 'location', 'logged_in_at',
    ];

    protected function casts(): array
    {
        return ['logged_in_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
