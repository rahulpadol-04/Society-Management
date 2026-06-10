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

/**
 * Represents a person (owner, tenant, or family member) registered against
 * a flat in the society. Family members are linked via `parent_id`.
 */
class Resident extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'user_id', 'flat_id', 'parent_id',
        'type', 'name', 'email', 'phone', 'relation',
        'is_primary', 'photo', 'move_in_date', 'move_out_date',
        'status', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_primary'    => 'boolean',
            'move_in_date'  => 'date',
            'move_out_date' => 'date',
            'meta'          => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** Linked platform user account (soft cross-module link). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Flat this resident lives in (soft cross-module link). */
    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class, 'flat_id');
    }

    /** Parent resident record (for family members). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'parent_id');
    }

    /** Family members linked to this resident as parent. */
    public function familyMembers(): HasMany
    {
        return $this->hasMany(Resident::class, 'parent_id')
            ->where('type', 'family_member');
    }

    /** Emergency contacts registered for this resident. */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /** Vehicles owned / operated by this resident (soft link). */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    // -------------------------------------------------------------------------
    // Accessors / helpers
    // -------------------------------------------------------------------------

    /** Human-readable display label for select lists and headings. */
    public function getDisplayLabelAttribute(): string
    {
        $flat = $this->flat?->number ? ' ('.$this->flat->number.')' : '';

        return $this->name.$flat;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
