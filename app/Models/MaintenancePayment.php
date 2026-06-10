<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenancePayment extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'receipt_number', 'maintenance_bill_id',
        'amount', 'method', 'reference', 'paid_at', 'recorded_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'float',
            'paid_at' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(MaintenanceBill::class, 'maintenance_bill_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by')->withoutGlobalScopes();
    }
}
