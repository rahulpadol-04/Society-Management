<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A Society is the SaaS tenant. All tenant-scoped data references it via
 * society_id. The platform (Super Admin) is the only actor that operates
 * across societies.
 */
class Society extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'registration_number', 'email', 'phone', 'logo',
        'address_line1', 'address_line2', 'city', 'state', 'country', 'postal_code',
        'latitude', 'longitude', 'timezone', 'currency',
        'current_plan_id', 'subscription_status', 'trial_ends_at', 'subscription_ends_at',
        'status', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings'             => 'array',
            'trial_ends_at'        => 'datetime',
            'subscription_ends_at' => 'datetime',
            'latitude'             => 'float',
            'longitude'            => 'float',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'current_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasMany
    {
        return $this->subscriptions()->whereIn('status', ['trial', 'active'])->latest();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class)->withoutGlobalScopes();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' || $this->subscription_status === 'suspended';
    }

    public function hasActiveSubscription(): bool
    {
        if (in_array($this->subscription_status, ['active', 'trial'], true)) {
            return is_null($this->subscription_ends_at) || $this->subscription_ends_at->isFuture()
                || ($this->subscription_status === 'trial' && optional($this->trial_ends_at)->isFuture());
        }

        return false;
    }

    /** Whether the society's current plan grants a feature flag. */
    public function hasFeature(string $feature): bool
    {
        $features = (array) ($this->plan?->features ?? []);

        return in_array($feature, $features, true);
    }
}
