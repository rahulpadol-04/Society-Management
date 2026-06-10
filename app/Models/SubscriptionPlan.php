<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'billing_cycle', 'price', 'currency',
        'trial_days', 'max_units', 'max_users', 'max_storage_mb', 'features',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features'    => 'array',
            'price'       => 'decimal:2',
            'is_active'   => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function societies(): HasMany
    {
        return $this->hasMany(Society::class, 'current_plan_id');
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, (array) $this->features, true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
