<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['society_id', 'tower_id', 'name', 'number'];

    public function tower(): BelongsTo
    {
        return $this->belongsTo(Tower::class);
    }

    public function flats(): HasMany
    {
        return $this->hasMany(Flat::class);
    }
}
