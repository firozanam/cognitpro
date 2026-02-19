<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Contracts\UserRepositoryInterface;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Find a user by ID.
     */
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update a user.
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Delete a user.
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get paginated users.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }

    /**
     * Check if email is already taken.
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $query = User::where('email', $email);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->exists();
    }
}
