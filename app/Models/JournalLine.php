<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'society_id', 'journal_entry_id', 'ledger_account_id',
        'debit', 'credit', 'memo',
    ];

    protected function casts(): array
    {
        return [
            'debit'  => 'float',
            'credit' => 'float',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }
}
