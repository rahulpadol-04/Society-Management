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

class StaffMember extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'user_id', 'name', 'employee_code', 'designation',
        'department', 'phone', 'email', 'joining_date', 'salary', 'shift',
        'status', 'photo', 'address',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'salary'       => 'float',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(StaffLeave::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
