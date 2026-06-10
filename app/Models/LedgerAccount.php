<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerAccount extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'code', 'name', 'type', 'subtype',
        'opening_balance', 'is_active', 'description',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'float',
            'is_active'       => 'boolean',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function bankAccount()
    {
        return $this->hasOne(BankAccount::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Net balance: for debit-normal accounts (asset, expense) balance = debits - credits + opening.
     * For credit-normal accounts (liability, equity, income) balance = credits - debits + opening.
     */
    public function balance(): float
    {
        $debits  = (float) $this->lines()->sum('debit');
        $credits = (float) $this->lines()->sum('credit');

        if (in_array($this->type, ['asset', 'expense'], true)) {
            return $this->opening_balance + $debits - $credits;
        }

        return $this->opening_balance + $credits - $debits;
    }
}
