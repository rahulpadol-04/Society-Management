<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscalationRule extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'level', 'name', 'after_hours', 'notify_role', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'level'       => 'integer',
            'after_hours' => 'integer',
        ];
    }
}
