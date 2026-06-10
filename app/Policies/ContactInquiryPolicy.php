<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ContactInquiry;
use App\Models\User;

/**
 * Governs platform-level ContactInquiry management. Super Admins bypass via Gate::before.
 */
class ContactInquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inquiries.view');
    }

    public function view(User $user, ContactInquiry $inquiry): bool
    {
        return $user->can('inquiries.view');
    }

    public function update(User $user, ContactInquiry $inquiry): bool
    {
        return $user->can('inquiries.update');
    }

    public function delete(User $user, ContactInquiry $inquiry): bool
    {
        return $user->can('inquiries.delete');
    }
}
