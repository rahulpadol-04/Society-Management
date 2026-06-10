<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A vehicle (car / bike / other) owned by a resident, optionally linked to a
 * flat and a parking slot. Cross-module FKs are soft links (no constrained()).
 */
class Vehicle extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'flat_id', 'resident_id', 'parking_slot_id',
        'type', 'make', 'model', 'registration_number', 'color',
        'rfid_tag', 'status',
    ];

    // -------------------------------------------------------------------------
    // Relationships (soft cross-module links — no constrained FK)
    // -------------------------------------------------------------------------

    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class, 'flat_id');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    public function parkingSlot(): BelongsTo
    {
        return $this->belongsTo(ParkingSlot::class, 'parking_slot_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
