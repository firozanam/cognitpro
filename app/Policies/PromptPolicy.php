<?php

namespace App\Policies;

use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;

class PromptPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Prompt $prompt): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSeller();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Prompt $prompt): bool
    {
        return $user->id === $prompt->seller_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Prompt $prompt): bool
    {
        return $user->id === $prompt->seller_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Prompt $prompt): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Prompt $prompt): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can approve the prompt.
     */
    public function approve(User $user, Prompt $prompt): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can feature the prompt.
     */
    public function feature(User $user, Prompt $prompt): bool
    {
        return $user->isAdmin();
    }
}
