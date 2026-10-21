<?php

namespace App\Policies;

use App\Models\Ebook;
use App\Models\User;

class EbookPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Ebook $ebook): bool
    {
        if ($ebook->status === 'published') {
            return true;
        }

        return $user !== null && ($user->isAdmin() || $ebook->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isAuthor() || $user->isAdmin();
    }

    public function update(User $user, Ebook $ebook): bool
    {
        return $user->isAdmin() || ($user->isAuthor() && (int) $ebook->user_id === (int) $user->id);
    }

    public function delete(User $user, Ebook $ebook): bool
    {
        return $user->isAdmin() || ($user->isAuthor() && (int) $ebook->user_id === (int) $user->id);
    }
}
