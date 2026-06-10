<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SocietyDocument;
use App\Models\User;

class SocietyDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.view');
    }

    public function view(User $user, SocietyDocument $document): bool
    {
        if ($document->is_public) {
            return true;
        }

        return $user->can('documents.view');
    }

    public function create(User $user): bool
    {
        return $user->can('documents.create');
    }

    public function delete(User $user, SocietyDocument $document): bool
    {
        return $user->can('documents.delete');
    }
}
