<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintCategory extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['society_id', 'name', 'slug', 'sla_hours', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
