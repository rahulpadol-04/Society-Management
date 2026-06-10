<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintFeedback extends Model
{
    use BelongsToTenant;

    protected $table = 'complaint_feedback';

    protected $fillable = ['society_id', 'complaint_id', 'user_id', 'rating', 'comment'];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
