<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'reference', 'vendor_id', 'complaint_id', 'title',
        'description', 'priority', 'status', 'amount', 'scheduled_for',
        'completed_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'date',
            'completed_at'  => 'datetime',
            'amount'        => 'float',
        ];
    }

    /** Soft link to vendor — vendor may be deleted without cascading. */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id')->withTrashed();
    }

    /** Soft link to the user who created this work order. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
