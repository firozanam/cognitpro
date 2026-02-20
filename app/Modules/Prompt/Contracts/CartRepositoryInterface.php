<?php

namespace App\Modules\Prompt\Contracts;

use App\Modules\Prompt\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

interface CartRepositoryInterface
{
    /**
     * Get all cart items for a user.
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get cart item count for a user.
     */
    public function getCountByUserId(int $userId): int;

    /**
     * Find a cart item by user and prompt.
     */
    public function findByUserAndPrompt(int $userId, int $promptId): ?Cart;

    /**
     * Find a cart item by ID.
     */
    public function find(int $id): ?Cart;

    /**
     * Create a cart item.
     */
    public function create(array $data): Cart;

    /**
     * Update a cart item.
     */
    public function update(Cart $cart, array $data): Cart;

    /**
     * Delete a cart item.
     */
    public function delete(Cart $cart): bool;

    /**
     * Delete all cart items for a user.
     */
    public function deleteByUserId(int $userId): bool;

    /**
     * Check if a prompt is in the user's cart.
     */
    public function hasPrompt(int $userId, int $promptId): bool;

    /**
     * Get cart items with prompt details for a user.
     */
    public function getWithPrompts(int $userId): Collection;

    /**
     * Get total cart value for a user.
     */
    public function getTotalByUserId(int $userId): float;
}
