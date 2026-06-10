<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BankAccount;
use App\Models\User;

class BankAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.view');
    }

    public function view(User $user, BankAccount $bankAccount): bool
    {
        return $user->can('accounting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.create');
    }

    public function update(User $user, BankAccount $bankAccount): bool
    {
        return $user->can('accounting.update');
    }

    public function delete(User $user, BankAccount $bankAccount): bool
    {
        return $user->can('accounting.delete');
    }
}
