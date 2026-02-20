<?php

namespace App\Modules\Prompt\Repositories;

use App\Modules\Prompt\Contracts\CartRepositoryInterface;
use App\Modules\Prompt\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

class CartRepository implements CartRepositoryInterface
{
    /**
     * Get all cart items for a user.
     */
    public function getByUserId(int $userId): Collection
    {
        return Cart::where('user_id', $userId)->get();
    }

    /**
     * Get cart item count for a user.
     */
    public function getCountByUserId(int $userId): int
    {
        return Cart::where('user_id', $userId)->count();
    }

    /**
     * Find a cart item by user and prompt.
     */
    public function findByUserAndPrompt(int $userId, int $promptId): ?Cart
    {
        return Cart::where('user_id', $userId)
            ->where('prompt_id', $promptId)
            ->first();
    }

    /**
     * Find a cart item by ID.
     */
    public function find(int $id): ?Cart
    {
        return Cart::find($id);
    }

    /**
     * Create a cart item.
     */
    public function create(array $data): Cart
    {
        return Cart::create($data);
    }

    /**
     * Update a cart item.
     */
    public function update(Cart $cart, array $data): Cart
    {
        $cart->update($data);
        return $cart->fresh();
    }

    /**
     * Delete a cart item.
     */
    public function delete(Cart $cart): bool
    {
        return $cart->delete();
    }

    /**
     * Delete all cart items for a user.
     */
    public function deleteByUserId(int $userId): bool
    {
        return Cart::where('user_id', $userId)->delete() > 0;
    }

    /**
     * Check if a prompt is in the user's cart.
     */
    public function hasPrompt(int $userId, int $promptId): bool
    {
        return Cart::where('user_id', $userId)
            ->where('prompt_id', $promptId)
            ->exists();
    }

    /**
     * Get cart items with prompt details for a user.
     */
    public function getWithPrompts(int $userId): Collection
    {
        return Cart::where('user_id', $userId)
            ->with(['prompt.seller', 'prompt.category', 'prompt.tags'])
            ->get();
    }

    /**
     * Get total cart value for a user.
     */
    public function getTotalByUserId(int $userId): float
    {
        return (float) Cart::where('user_id', $userId)
            ->join('prompts', 'carts.prompt_id', '=', 'prompts.id')
            ->sum('prompts.price');
    }
}
