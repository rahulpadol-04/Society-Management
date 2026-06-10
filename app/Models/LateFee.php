<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LateFee extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'maintenance_bill_id', 'amount', 'reason', 'applied_on',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'float',
            'applied_on' => 'date',
        ];
    }

    public function maintenanceBill(): BelongsTo
    {
        return $this->belongsTo(MaintenanceBill::class);
    }
}
