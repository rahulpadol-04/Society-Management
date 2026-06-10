<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use Auditable, BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'name', 'depreciation_rate', 'useful_life_years', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'depreciation_rate'  => 'float',
            'useful_life_years'  => 'integer',
            'is_active'          => 'boolean',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
