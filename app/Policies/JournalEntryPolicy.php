<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.view');
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('accounting.view');
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.create');
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('accounting.update');
    }

    public function post(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('accounting.post');
    }

    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('accounting.delete') && $journalEntry->status === 'draft';
    }
}
