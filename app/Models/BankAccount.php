<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'ledger_account_id', 'name', 'account_type',
        'bank_name', 'account_number', 'ifsc',
        'opening_balance', 'current_balance', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'float',
            'current_balance' => 'float',
            'is_active'       => 'boolean',
        ];
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id')->withoutGlobalScopes();
    }
}
