<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitorPass extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id',
        'code',
        'flat_id',
        'host_id',
        'name',
        'phone',
        'type',
        'purpose',
        'vehicle_number',
        'expected_at',
        'valid_until',
        'max_entries',
        'entries_used',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_at' => 'datetime',
            'valid_until' => 'datetime',
            'approved_at' => 'datetime',
            'max_entries' => 'integer',
            'entries_used' => 'integer',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /** Soft-link to the flat (cross-module — no FK constraint). */
    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class, 'flat_id')->withoutGlobalScopes();
    }

    /** The resident who created / owns this pass. */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id')->withoutGlobalScopes();
    }

    /** The staff/admin who approved this pass. */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withoutGlobalScopes();
    }

    /** All gate-entry records against this pass. */
    public function logs(): HasMany
    {
        return $this->hasMany(VisitorLog::class)->latest('checked_in_at');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** True when the pass can be used for a new gate entry right now. */
    public function isUsable(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return $this->entries_used < $this->max_entries;
    }
}
