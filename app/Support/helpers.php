<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\Society;
use App\Support\Tenancy\TenantManager;

if (! function_exists('tenancy')) {
    /** Resolve the tenant manager singleton. */
    function tenancy(): TenantManager
    {
        return app('tenancy');
    }
}

if (! function_exists('current_society')) {
    function current_society(): ?Society
    {
        return tenancy()->current();
    }
}

if (! function_exists('current_society_id')) {
    function current_society_id(): ?int
    {
        return tenancy()->id();
    }
}

if (! function_exists('setting')) {
    /**
     * Read a tenant-scoped setting with a sensible default. Cached per tenant.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('money')) {
    /** Format an amount as INR currency (society default). */
    function money(int|float|null $amount, string $symbol = '₹'): string
    {
        return $symbol.number_format((float) ($amount ?? 0), 2);
    }
}

if (! function_exists('feature_enabled')) {
    /** Whether the current tenant's subscription grants a plan feature. */
    function feature_enabled(string $feature): bool
    {
        $society = current_society();

        return $society ? $society->hasFeature($feature) : false;
    }
}

if (! function_exists('money_short')) {
    /**
     * Compact currency for stat tiles using the Indian numbering system
     * (e.g. 1025880 -> "₹10.26L", 12500000 -> "₹1.25Cr"). Falls back to a
     * plain formatted amount under 1 lakh.
     */
    function money_short(int|float|null $amount, string $symbol = '₹'): string
    {
        $value = (float) ($amount ?? 0);
        $abs = abs($value);

        return match (true) {
            $abs >= 10000000 => $symbol.rtrim(rtrim(number_format($value / 10000000, 2), '0'), '.').'Cr',
            $abs >= 100000   => $symbol.rtrim(rtrim(number_format($value / 100000, 2), '0'), '.').'L',
            $abs >= 1000     => $symbol.rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'K',
            default          => $symbol.number_format($value),
        };
    }
}
