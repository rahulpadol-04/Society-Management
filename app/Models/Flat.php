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

class Flat extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'tower_id', 'floor_id', 'number', 'type',
        'carpet_area', 'built_up_area', 'bedrooms', 'bathrooms',
        'ownership', 'status', 'owner_id', 'maintenance_amount', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'carpet_area'        => 'float',
            'built_up_area'      => 'float',
            'maintenance_amount' => 'float',
            'meta'               => 'array',
        ];
    }

    public function tower(): BelongsTo
    {
        return $this->belongsTo(Tower::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function parkingSlots(): HasMany
    {
        return $this->hasMany(ParkingSlot::class);
    }

    public function getLabelAttribute(): string
    {
        return trim(($this->tower?->code ?? $this->tower?->name ?? '').' '.$this->number);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }
}
