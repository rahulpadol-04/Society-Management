<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tower extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'name', 'code', 'type', 'total_floors', 'units_per_floor',
        'description', 'status',
    ];

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class)->orderBy('number');
    }

    public function flats(): HasMany
    {
        return $this->hasMany(Flat::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
