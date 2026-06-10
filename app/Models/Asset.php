<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'asset_category_id', 'code', 'name', 'description', 'location',
        'tower_id', 'vendor_id', 'purchase_date', 'purchase_cost', 'salvage_value',
        'depreciation_method', 'depreciation_rate', 'useful_life_years', 'current_value',
        'status', 'warranty_until', 'image',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date'    => 'date',
            'warranty_until'   => 'date',
            'purchase_cost'    => 'float',
            'salvage_value'    => 'float',
            'current_value'    => 'float',
            'depreciation_rate' => 'float',
            'useful_life_years' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function tower(): BelongsTo
    {
        return $this->belongsTo(Tower::class, 'tower_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(AssetMaintenanceSchedule::class)->latest();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AssetMaintenanceLog::class)->latest('performed_on');
    }

    public function ageInYears(?Carbon $asOf = null): float
    {
        if (! $this->purchase_date) {
            return 0.0;
        }

        $asOf ??= now();

        return (float) $this->purchase_date->diffInDays($asOf) / 365.25;
    }

    public function depreciatedValue(?Carbon $asOf = null): float
    {
        $method = $this->depreciation_method ?? 'straight_line';

        if ($method === 'none') {
            return (float) ($this->purchase_cost ?? 0);
        }

        $cost    = (float) ($this->purchase_cost ?? 0);
        $salvage = (float) ($this->salvage_value ?? 0);
        $years   = $this->ageInYears($asOf);

        // Use asset-level rate/life first, fall back to category.
        $life = (int) ($this->useful_life_years
            ?? $this->category?->useful_life_years
            ?? 0);

        $rate = (float) ($this->depreciation_rate
            ?? $this->category?->depreciation_rate
            ?? 0);

        if ($method === 'straight_line') {
            if ($life <= 0) {
                return $cost;
            }

            $annualDepreciation = ($cost - $salvage) / $life;
            $accumulated        = $annualDepreciation * $years;
            $value              = $cost - $accumulated;

            return max($salvage, round($value, 2));
        }

        // declining_balance
        if ($rate <= 0) {
            return $cost;
        }

        $rateDecimal = $rate / 100;
        $value       = $cost * (1 - $rateDecimal) ** $years;

        return max($salvage, round($value, 2));
    }
}
