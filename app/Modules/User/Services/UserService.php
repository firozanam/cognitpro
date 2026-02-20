<?php

namespace App\Modules\User\Services;

use App\Modules\User\Contracts\UserRepositoryInterface;
use App\Modules\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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
     * Get paginated users.
     */
    public function getPaginatedUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
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
        return !$this->userRepository->emailExists($email, $excludeUserId);
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
