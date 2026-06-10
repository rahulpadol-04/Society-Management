<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    use Auditable, BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id',
        'visitor_pass_id',
        'flat_id',
        'guard_id',
        'name',
        'phone',
        'type',
        'purpose',
        'vehicle_number',
        'photo',
        'id_proof',
        'gate',
        'checked_in_at',
        'checked_out_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at'  => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /** The pre-approved pass that this log entry was made against (nullable). */
    public function pass(): BelongsTo
    {
        return $this->belongsTo(VisitorPass::class, 'visitor_pass_id');
    }

    /** Soft-link to the flat (cross-module — no FK constraint). */
    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class, 'flat_id')->withoutGlobalScopes();
    }

    /**
     * The security guard who performed the check-in. Named guardUser() (not
     * guard()) to avoid clashing with Eloquent's reserved Model::guard().
     */
    public function guardUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_id')->withoutGlobalScopes();
    }
}
