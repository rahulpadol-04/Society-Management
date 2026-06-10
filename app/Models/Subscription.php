<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'society_id', 'subscription_plan_id', 'status', 'amount', 'currency',
        'billing_cycle', 'starts_at', 'ends_at', 'cancelled_at',
        'gateway', 'gateway_subscription_id', 'last_payment_id', 'last_payment_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'       => 'datetime',
            'ends_at'         => 'datetime',
            'cancelled_at'    => 'datetime',
            'last_payment_at' => 'datetime',
            'amount'          => 'decimal:2',
            'meta'            => 'array',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active'], true)
            && (is_null($this->ends_at) || $this->ends_at->isFuture());
    }
}
