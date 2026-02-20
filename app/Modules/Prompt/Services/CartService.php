<?php

namespace App\Modules\Prompt\Services;

use App\Modules\Prompt\Contracts\CartRepositoryInterface;
use App\Modules\Prompt\Contracts\PurchaseRepositoryInterface;
use App\Modules\Prompt\Models\Cart;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class CartService
{
    protected CartRepositoryInterface $cartRepository;
    protected PurchaseRepositoryInterface $purchaseRepository;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        PurchaseRepositoryInterface $purchaseRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->purchaseRepository = $purchaseRepository;
    }

    /**
     * Get cart items for a user.
     */
    public function getCartItems(int $userId): Collection
    {
        return $this->cartRepository->getWithPrompts($userId);
    }

    /**
     * Get cart item count for a user.
     */
    public function getCartCount(int $userId): int
    {
        return $this->cartRepository->getCountByUserId($userId);
    }

    /**
     * Get cart summary for a user.
     */
    public function getCartSummary(int $userId): array
    {
        $items = $this->cartRepository->getWithPrompts($userId);
        $total = 0;
        $validItems = 0;

        foreach ($items as $item) {
            if ($item->prompt && $item->prompt->isApproved()) {
                $total += $item->prompt->price;
                $validItems++;
            }
        }

        return [
            'items' => $items,
            'total' => $total,
            'count' => $validItems,
        ];
    }

    /**
     * Add a prompt to the cart.
     */
    public function addToCart(User $user, Prompt $prompt): Cart
    {
        // Validate prompt can be added
        if (!$prompt->isApproved()) {
            throw new Exception('This prompt is not available for purchase.');
        }

        // Check if user is the seller
        if ($user->id === $prompt->seller_id) {
            throw new Exception('You cannot add your own prompt to the cart.');
        }

        // Check if already purchased
        if ($this->purchaseRepository->hasPurchased($user->id, $prompt->id)) {
            throw new Exception('You already own this prompt.');
        }

        // Check if already in cart
        if ($this->cartRepository->hasPrompt($user->id, $prompt->id)) {
            throw new Exception('This prompt is already in your cart.');
        }

        return $this->cartRepository->create([
            'user_id' => $user->id,
            'prompt_id' => $prompt->id,
            'price' => $prompt->price,
        ]);
    }

    /**
     * Remove a prompt from the cart.
     */
    public function removeFromCart(int $userId, int $promptId): bool
    {
        $cartItem = $this->cartRepository->findByUserAndPrompt($userId, $promptId);

        if (!$cartItem) {
            throw new Exception('Item not found in cart.');
        }

        return $this->cartRepository->delete($cartItem);
    }

    /**
     * Remove a cart item by ID.
     */
    public function removeCartItem(int $userId, int $cartId): bool
    {
        $cartItem = $this->cartRepository->find($cartId);

        if (!$cartItem || $cartItem->user_id !== $userId) {
            throw new Exception('Cart item not found.');
        }

        return $this->cartRepository->delete($cartItem);
    }

    /**
     * Clear all items from the cart.
     */
    public function clearCart(int $userId): bool
    {
        return $this->cartRepository->deleteByUserId($userId);
    }

    /**
     * Check if a prompt is in the user's cart.
     */
    public function isInCart(int $userId, int $promptId): bool
    {
        return $this->cartRepository->hasPrompt($userId, $promptId);
    }

    /**
     * Validate cart items before checkout.
     */
    public function validateCart(int $userId): array
    {
        $items = $this->cartRepository->getWithPrompts($userId);
        $errors = [];
        $validItems = [];
        $total = 0;

        foreach ($items as $item) {
            $prompt = $item->prompt;

            // Check if prompt still exists and is approved
            if (!$prompt) {
                $errors[] = "A prompt in your cart is no longer available.";
                $this->cartRepository->delete($item);
                continue;
            }

            if (!$prompt->isApproved()) {
                $errors[] = "\"{$prompt->title}\" is no longer available for purchase.";
                $this->cartRepository->delete($item);
                continue;
            }

            // Check if already purchased
            if ($this->purchaseRepository->hasPurchased($userId, $prompt->id)) {
                $errors[] = "You already own \"{$prompt->title}\".";
                $this->cartRepository->delete($item);
                continue;
            }

            $validItems[] = $item;
            $total += $prompt->price;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'items' => collect($validItems),
            'total' => $total,
        ];
    }
}
