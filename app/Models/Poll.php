<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'notice_id', 'question', 'description',
        'multiple_choice', 'closes_at', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'multiple_choice' => 'boolean',
            'is_active'       => 'boolean',
            'closes_at'       => 'datetime',
        ];
    }

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function hasVoted(int $userId): bool
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }

    public function totalVotes(): int
    {
        return (int) $this->options()->sum('votes_count');
    }
}
