<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\AuditLog;

/**
 * Records create / update / delete activity for a model into the audit_logs
 * table, capturing the acting user, the society (tenant), the changed
 * attributes and request metadata. Add `protected array $auditExclude` to a
 * model to omit noisy columns from the diff.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => $model->recordAudit('created', [], $model->auditableAttributes()));

        static::updated(function ($model): void {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if ($changes !== []) {
                $original = array_intersect_key($model->getOriginal(), $changes);
                $model->recordAudit('updated', $original, $changes);
            }
        });

        static::deleted(fn ($model) => $model->recordAudit('deleted', $model->auditableAttributes(), []));
    }

    public function auditableAttributes(): array
    {
        $exclude = array_merge(
            ['password', 'remember_token', 'two_factor_secret', 'created_at', 'updated_at'],
            property_exists($this, 'auditExclude') ? $this->auditExclude : []
        );

        return collect($this->getAttributes())->except($exclude)->all();
    }

    public function recordAudit(string $event, array $old, array $new): void
    {
        AuditLog::query()->create([
            'society_id'   => app('tenancy')->id(),
            'user_id'      => auth()->id(),
            'event'        => $event,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'old_values'   => $old,
            'new_values'   => $new,
            'ip_address'   => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 500),
            'url'          => substr((string) request()->fullUrl(), 0, 1000),
        ]);
    }

    public function audits()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
