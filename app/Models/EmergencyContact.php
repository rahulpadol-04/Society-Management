<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Emergency contact for a resident — not a platform user, just contact info.
 */
class EmergencyContact extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'society_id', 'resident_id', 'name', 'phone', 'relation', 'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
