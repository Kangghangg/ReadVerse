<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return $user->isReader() || $user->isAuthor() || $user->isAdmin();
    }

    public function update(User $user, Review $review): bool
    {
        return (int) $review->user_id === (int) $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin() || (int) $review->user_id === (int) $user->id;
    }
}
