<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingSlot extends Model
{
    use Auditable, BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'code', 'type', 'location', 'flat_id', 'vehicle_id', 'status',
    ];

    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
