<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use Auditable, BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'staff_member_id', 'period', 'basic', 'allowances',
        'deductions', 'net', 'days_present', 'days_absent', 'status', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'basic'       => 'float',
            'allowances'  => 'float',
            'deductions'  => 'float',
            'net'         => 'float',
            'paid_at'     => 'datetime',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}
