<?php

namespace App\Policies;

use App\Models\User;

class CommentPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, mixed $comment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isReader() || $user->isAuthor() || $user->isAdmin();
    }

    public function update(User $user, mixed $comment): bool
    {
        return $user->isAdmin() || $user->id === ($comment->user_id ?? null);
    }

    public function delete(User $user, mixed $comment): bool
    {
        return $user->isAdmin() || $user->id === ($comment->user_id ?? null);
    }
}
