<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'name', 'company', 'category', 'contact_person',
        'phone', 'email', 'gstin', 'address', 'rating', 'ratings_count',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'rating'        => 'float',
            'ratings_count' => 'integer',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(VendorContract::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(VendorRating::class);
    }

    public function recalcRating(): void
    {
        $aggregate = VendorRating::where('vendor_id', $this->id)->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')->first();

        $this->update([
            'rating'        => round((float) ($aggregate->avg_rating ?? 0), 2),
            'ratings_count' => (int) ($aggregate->total ?? 0),
        ]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
