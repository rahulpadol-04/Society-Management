<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceHead extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'name', 'code', 'type', 'amount',
        'is_taxable', 'gst_percentage', 'frequency',
        'is_active', 'description',
    ];

    protected function casts(): array
    {
        return [
            'amount'         => 'float',
            'gst_percentage' => 'float',
            'is_taxable'     => 'boolean',
            'is_active'      => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
