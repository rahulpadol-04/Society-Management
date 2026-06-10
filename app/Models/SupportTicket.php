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

class SupportTicket extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'ticket_number', 'subject', 'description',
        'category', 'priority', 'status',
        'raised_by', 'assigned_to',
        'sla_due_at', 'sla_breached', 'escalation_level',
        'resolved_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'sla_breached'    => 'boolean',
            'is_internal'     => 'boolean',
            'sla_due_at'      => 'datetime',
            'resolved_at'     => 'datetime',
            'closed_at'       => 'datetime',
            'escalation_level' => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by')->withoutGlobalScopes();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to')->withoutGlobalScopes();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'support_ticket_id')->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class, 'support_ticket_id')->latest();
    }

    // -----------------------------------------------------------------------
    // Business helpers
    // -----------------------------------------------------------------------

    public function isOverdue(): bool
    {
        return $this->sla_due_at
            && $this->sla_due_at->isPast()
            && ! in_array($this->status, ['resolved', 'closed'], true);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }
}
