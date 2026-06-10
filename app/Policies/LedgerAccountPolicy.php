<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LedgerAccount;
use App\Models\User;

class LedgerAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.view');
    }

    public function view(User $user, LedgerAccount $ledgerAccount): bool
    {
        return $user->can('accounting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.create');
    }

    public function update(User $user, LedgerAccount $ledgerAccount): bool
    {
        return $user->can('accounting.update');
    }

    public function delete(User $user, LedgerAccount $ledgerAccount): bool
    {
        return $user->can('accounting.delete');
    }
}
