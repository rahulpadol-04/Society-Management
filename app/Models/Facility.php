<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'name', 'type', 'description', 'capacity', 'charge',
        'requires_approval', 'opening_time', 'closing_time', 'slot_minutes',
        'is_active', 'image',
    ];

    protected function casts(): array
    {
        return [
            'charge'            => 'float',
            'requires_approval' => 'boolean',
            'is_active'         => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(FacilityBooking::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
