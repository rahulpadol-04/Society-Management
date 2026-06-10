<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Platform-level payment gateway configuration (global, no tenant scope).
 */
class PaymentGateway extends Model
{
    protected $fillable = [
        'name', 'provider', 'mode', 'is_active', 'credentials',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'is_active'   => 'boolean',
        ];
    }
}
