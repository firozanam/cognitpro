<?php

namespace App\Modules\User\Services;

use App\Modules\User\Contracts\UserRepositoryInterface;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get a user by ID.
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Get paginated users, optionally filtered by search term and role.
     */
    public function getPaginatedUsers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    /**
     * Update a user's role with self-target and last-admin guards (BIZ-08).
     *
     * @throws RuntimeException
     */
    public function updateUserRole(User $actor, User $target, string $role): User
    {
        if ($actor->id === $target->id) {
            throw new \RuntimeException('You cannot change your own role.');
        }

        $demotingAdmin = $target->isAdmin() && $role !== 'admin';

        if ($demotingAdmin && User::where('role', 'admin')->count() <= 1) {
            throw new \RuntimeException('You cannot demote the last remaining admin.');
        }

        $this->userRepository->update($target, ['role' => $role]);

        return $target->refresh();
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        // Business logic: reset email verification if email changed
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        $this->userRepository->update($user, $data);

        return $user->refresh();
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $password): bool
    {
        return $this->userRepository->update($user, [
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(User $user): bool
    {
        // Business logic: add any cleanup operations here
        // e.g., delete related records, notify services, etc.

        return $this->userRepository->delete($user);
    }

    /**
     * Check if email is available.
     */
    public function isEmailAvailable(string $email, ?int $excludeUserId = null): bool
    {
        return ! $this->userRepository->emailExists($email, $excludeUserId);
    }

    /**
     * Get total user count.
     */
    public function getTotalUsers(): int
    {
        return User::count();
    }

    /**
     * Get total seller count.
     */
    public function getTotalSellers(): int
    {
        return User::where('role', 'seller')->count();
    }

    /**
     * Get recent users.
     */
    public function getRecentUsers(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('profile')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
