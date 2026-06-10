<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'title', 'body', 'category', 'author_id', 'audience',
        'is_published', 'published_at', 'pinned', 'event_at', 'attachment',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'pinned'       => 'boolean',
            'published_at' => 'datetime',
            'event_at'     => 'datetime',
        ];
    }

    /**
     * The user who authored this notice. Uses withoutGlobalScopes so super-admin
     * cross-tenant queries don't lose the author reference.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withoutGlobalScopes();
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('pinned', true);
    }
}
