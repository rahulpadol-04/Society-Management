<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A Role groups permissions. Society-scoped roles are duplicated per tenant so
 * each society can freely customise which permissions a role carries — this is
 * what makes the RBAC "dynamic and configurable". Global roles (society_id
 * null) such as Super Admin are shared platform-wide.
 *
 * Note: Role is intentionally NOT tenant-globally-scoped; queries filter by
 * society_id explicitly where needed so the seeder and Super Admin can manage
 * roles across societies.
 */
class Role extends Model
{
    protected $fillable = [
        'society_id', 'name', 'slug', 'scope', 'level', 'description', 'is_system',
    ];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    public function scopeForSociety($query, ?int $societyId)
    {
        return $query->where(function ($q) use ($societyId) {
            $q->where('society_id', $societyId)->orWhereNull('society_id');
        });
    }
}
