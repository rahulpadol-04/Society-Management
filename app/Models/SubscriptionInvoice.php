<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'society_id', 'subscription_id', 'invoice_number', 'amount', 'tax', 'total',
        'currency', 'status', 'gateway', 'gateway_payment_id', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'tax'     => 'decimal:2',
            'total'   => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
