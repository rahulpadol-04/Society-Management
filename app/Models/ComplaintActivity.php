<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintActivity extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'society_id', 'complaint_id', 'user_id', 'action', 'from_status', 'to_status', 'note',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
