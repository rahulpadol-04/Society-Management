<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffLeave extends Model
{
    use Auditable, BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'staff_member_id', 'type', 'from_date', 'to_date',
        'days', 'reason', 'status', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date'   => 'date',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}
