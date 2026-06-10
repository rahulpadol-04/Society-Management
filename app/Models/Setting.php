<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Key/value settings with tenant overrides. Lookup precedence:
 *   1. current society's setting
 *   2. global (society_id NULL) setting
 *   3. provided default
 * Values are cached per tenant and typed via the `type` column.
 */
class Setting extends Model
{
    protected $fillable = ['society_id', 'group', 'key', 'value', 'type', 'is_public'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $societyId = app('tenancy')->id();
        $cacheKey = app('tenancy')->cacheKey("setting:{$key}");

        return Cache::remember($cacheKey, now()->addHour(), function () use ($key, $societyId, $default) {
            $row = static::query()
                ->where('key', $key)
                ->where(fn ($q) => $q->where('society_id', $societyId)->orWhereNull('society_id'))
                ->orderByRaw('society_id IS NULL')   // prefer the tenant-specific row
                ->first();

            return $row ? $row->typedValue() : $default;
        });
    }

    public static function put(string $key, mixed $value, string $type = 'string', string $group = 'general', ?int $societyId = null): self
    {
        $societyId ??= app('tenancy')->id();

        $stored = $type === 'json' ? json_encode($value)
            : ($type === 'encrypted' ? Crypt::encryptString((string) $value)
            : ($type === 'bool' ? ($value ? '1' : '0') : (string) $value));

        $setting = static::updateOrCreate(
            ['society_id' => $societyId, 'key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group]
        );

        Cache::forget(app('tenancy')->cacheKey("setting:{$key}"));

        return $setting;
    }

    public function typedValue(): mixed
    {
        return match ($this->type) {
            'int'       => (int) $this->value,
            'bool'      => (bool) $this->value,
            'json'      => json_decode((string) $this->value, true),
            'encrypted' => $this->value ? Crypt::decryptString($this->value) : null,
            default     => $this->value,
        };
    }
}
