<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'reference', 'complaint_category_id', 'raised_by', 'assigned_to',
        'flat_id', 'title', 'description', 'priority', 'status', 'attachments',
        'sla_due_at', 'sla_breached', 'assigned_at', 'resolved_at', 'closed_at', 'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'attachments'  => 'array',
            'sla_breached' => 'boolean',
            'sla_due_at'   => 'datetime',
            'assigned_at'  => 'datetime',
            'resolved_at'  => 'datetime',
            'closed_at'    => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'complaint_category_id');
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ComplaintActivity::class)->latest();
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(ComplaintFeedback::class);
    }

    public function isOverdue(): bool
    {
        return $this->sla_due_at
            && $this->sla_due_at->isPast()
            && ! in_array($this->status, ['resolved', 'closed'], true);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }
}
