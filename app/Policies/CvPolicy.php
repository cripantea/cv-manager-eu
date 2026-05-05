<?php

namespace App\Policies;

use App\Models\Cv;
use App\Models\User;

class CvPolicy
{
    public function view(User $user, Cv $cv): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $cv->user_id === $user->id && $cv->status !== 'archived';
    }

    public function update(User $user, Cv $cv): bool
    {
        return $cv->user_id === $user->id && $cv->status === 'draft';
    }

    public function lock(User $user, Cv $cv): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Cv $cv): bool
    {
        return $user->isAdmin();
    }
}
