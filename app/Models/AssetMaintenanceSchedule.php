<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetMaintenanceSchedule extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'asset_id', 'title', 'frequency', 'next_due_date', 'last_done_date',
        'assigned_to', 'vendor_id', 'estimated_cost', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'next_due_date'  => 'date',
            'last_done_date' => 'date',
            'estimated_cost' => 'float',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
