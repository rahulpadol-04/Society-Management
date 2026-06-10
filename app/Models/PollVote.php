<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'poll_id', 'poll_option_id', 'user_id',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function pollOption(): BelongsTo
    {
        return $this->belongsTo(PollOption::class);
    }
}
