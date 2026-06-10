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

class JournalEntry extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'reference', 'entry_date', 'narration', 'type', 'status',
        'amount', 'created_by', 'posted_by', 'posted_at', 'source',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at'  => 'datetime',
            'amount'     => 'float',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by')->withoutGlobalScopes();
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function totalDebit(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    public function totalCredit(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->totalDebit() - $this->totalCredit()) < 0.001;
    }
}
