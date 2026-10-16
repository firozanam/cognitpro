<?php

namespace App\Modules\Prompt\Services;

use App\Modules\Prompt\Contracts\CartRepositoryInterface;
use App\Modules\Prompt\Models\Cart;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Exception;
use Illuminate\Session\Store;
use Illuminate\Support\Collection;

/**
 * Carts for unauthenticated visitors live in the session as prompt ids.
 * They are merged into the user's cart on login (MergeGuestCart listener).
 */
class GuestCartService
{
    protected CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Get the guest cart prompt ids from the session.
     *
     * @return list<int>
     */
    public function getIds(Store $session): array
    {
        return array_values(array_map(intval(...), (array) $session->get('guest_cart', [])));
    }

    /**
     * Add a prompt to the guest cart.
     */
    public function add(Store $session, Prompt $prompt): void
    {
        if (! $prompt->isApproved()) {
            throw new Exception('This prompt is not available for purchase.');
        }

        // Pay-what-you-want needs a custom price, which the cart cannot
        // collect — it must be purchased from the prompt page.
        if ($prompt->isPayWhatYouWant()) {
            throw new Exception('Pay-what-you-want prompts are purchased directly from the prompt page.');
        }

        $ids = $this->getIds($session);

        if (in_array($prompt->id, $ids, true)) {
            throw new Exception('This prompt is already in your cart.');
        }

        $ids[] = $prompt->id;
        $session->put('guest_cart', $ids);
    }

    /**
     * Remove a prompt from the guest cart by prompt id.
     */
    public function remove(Store $session, int $promptId): void
    {
        $ids = $this->getIds($session);
        $remaining = array_values(array_diff($ids, [$promptId]));

        if (count($remaining) === count($ids)) {
            throw new Exception('Item not found in cart.');
        }

        $session->put('guest_cart', $remaining);
    }

    /**
     * Clear the guest cart.
     */
    public function clear(Store $session): void
    {
        $session->forget('guest_cart');
    }

    /**
     * Get the guest cart items as unpersisted cart models with the prompt
     * relation loaded, keeping the same shape the authenticated cart uses.
     * Unapproved prompts are hidden, never shown.
     */
    public function getItems(Store $session): Collection
    {
        $ids = $this->getIds($session);

        $prompts = Prompt::query()
            ->with(['seller', 'category'])
            ->whereIn('id', $ids)
            ->where('status', 'approved')
            ->get()
            ->keyBy('id');

        // Preserve the order in which prompts were added to the cart
        return collect($ids)
            ->map(fn (int $id) => $prompts->get($id))
            ->filter()
            ->map(function (Prompt $prompt) {
                $cart = new Cart([
                    'prompt_id' => $prompt->id,
                    'price' => $prompt->price,
                ]);
                // The frontend removes by item id; guests use the prompt id
                $cart->id = $prompt->id;
                $cart->setRelation('prompt', $prompt);

                return $cart;
            })
            ->values();
    }

    /**
     * Get the guest cart summary (items, total, count).
     */
    public function getSummary(Store $session): array
    {
        $items = $this->getItems($session);

        return [
            'items' => $items,
            'total' => round($items->sum(fn (Cart $item) => (float) $item->price), 2),
            'count' => $items->count(),
        ];
    }

    /**
     * Get the count of guest cart items whose prompt is still approved.
     * getItems hides unapproved prompts, so its item count is the valid count.
     */
    public function getValidCount(Store $session): int
    {
        return $this->getItems($session)->count();
    }

    /**
     * Merge the guest cart into a user's cart after login, skipping
     * duplicates and unapproved prompts, then clear the session cart.
     */
    public function mergeToUser(Store $session, User $user): void
    {
        $ids = $this->getIds($session);

        if ($ids === []) {
            return;
        }

        $prompts = Prompt::whereIn('id', $ids)->get();

        foreach ($prompts as $prompt) {
            if (! $prompt->isApproved()) {
                continue;
            }

            if ($this->cartRepository->hasPrompt($user->id, $prompt->id)) {
                continue;
            }

            $this->cartRepository->create([
                'user_id' => $user->id,
                'prompt_id' => $prompt->id,
                'price' => $prompt->price,
            ]);
        }

        $session->forget('guest_cart');
    }
}
