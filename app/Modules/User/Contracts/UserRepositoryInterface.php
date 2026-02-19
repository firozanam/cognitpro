<?php

namespace App\Modules\User\Contracts;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Find a user by ID.
     */
    public function find(int $id): ?User;

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     */
    public function create(array $data): User;

    /**
     * Update a user.
     */
    public function update(User $user, array $data): bool;

    /**
     * Delete a user.
     */
    public function delete(User $user): bool;

    /**
     * Get paginated users.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Check if email is already taken.
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool;
}
