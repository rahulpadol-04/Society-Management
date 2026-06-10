<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceBill extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'bill_number', 'flat_id', 'user_id', 'period',
        'bill_date', 'due_date', 'subtotal', 'tax_amount', 'late_fee',
        'discount', 'total', 'paid_amount', 'status', 'line_items', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'bill_date'   => 'date',
            'due_date'    => 'date',
            'subtotal'    => 'float',
            'tax_amount'  => 'float',
            'late_fee'    => 'float',
            'discount'    => 'float',
            'total'       => 'float',
            'paid_amount' => 'float',
            'line_items'  => 'array',
        ];
    }

    /** The flat this bill belongs to (soft link — no FK constraint). */
    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class)->withoutGlobalScopes();
    }

    /** The resident billed (soft link via user_id). */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MaintenancePayment::class);
    }

    public function lateFees(): HasMany
    {
        return $this->hasMany(LateFee::class);
    }

    /** Amount still owed on this bill. */
    public function getBalanceAttribute(): float
    {
        return (float) ($this->total - $this->paid_amount);
    }

    /** True when the due date has passed and the bill is not paid/cancelled. */
    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['paid', 'cancelled'], true);
    }
}
