<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityBooking extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'facility_id', 'user_id', 'flat_id', 'booking_date',
        'start_time', 'end_time', 'guests', 'amount', 'status', 'approved_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'float',
            'booking_date' => 'date',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function booker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class)->withoutGlobalScopes();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withoutGlobalScopes();
    }
}
