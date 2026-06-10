<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'poll_id', 'label', 'votes_count',
    ];

    protected function casts(): array
    {
        return [
            'votes_count' => 'integer',
        ];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }
}
