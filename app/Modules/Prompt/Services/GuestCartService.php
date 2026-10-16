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
 * Carts for unauthenticated visitors live in the session as a map of
 * prompt id to the chosen pay-what-you-want price (null otherwise).
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
        return array_map(intval(...), array_keys((array) $session->get('guest_cart', [])));
    }

    /**
     * Add a prompt to the guest cart.
     */
    public function add(Store $session, Prompt $prompt, ?float $price = null): void
    {
        if (! $prompt->isApproved()) {
            throw new Exception('This prompt is not available for purchase.');
        }

        // Pay-what-you-want prices are chosen by the buyer; fixed and free
        // prompts always use the prompt price.
        $cartPrice = null;

        if ($prompt->isPayWhatYouWant()) {
            if ($price === null) {
                throw new Exception('Choose a price before adding this prompt to the cart.');
            }

            $minimum = (float) ($prompt->min_price ?? 0);

            if ($price < $minimum) {
                throw new Exception('The minimum price for this prompt is '.number_format($minimum, 2).'.');
            }

            $cartPrice = $price;
        }

        $carts = (array) $session->get('guest_cart', []);

        if (array_key_exists($prompt->id, $carts)) {
            throw new Exception('This prompt is already in your cart.');
        }

        $carts[$prompt->id] = $cartPrice;
        $session->put('guest_cart', $carts);
    }

    /**
     * Remove a prompt from the guest cart by prompt id.
     */
    public function remove(Store $session, int $promptId): void
    {
        $carts = (array) $session->get('guest_cart', []);

        if (! array_key_exists($promptId, $carts)) {
            throw new Exception('Item not found in cart.');
        }

        unset($carts[$promptId]);
        $session->put('guest_cart', $carts);
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
        $carts = (array) $session->get('guest_cart', []);
        $ids = array_map(intval(...), array_keys($carts));

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
            ->map(function (Prompt $prompt) use ($carts) {
                $price = $prompt->isPayWhatYouWant()
                    ? (float) ($carts[$prompt->id] ?? $prompt->price)
                    : $prompt->price;

                $cart = new Cart([
                    'prompt_id' => $prompt->id,
                    'price' => $price,
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
        $carts = (array) $session->get('guest_cart', []);

        if ($carts === []) {
            return;
        }

        $ids = array_map(intval(...), array_keys($carts));
        $prompts = Prompt::whereIn('id', $ids)->get();

        foreach ($prompts as $prompt) {
            if (! $prompt->isApproved()) {
                continue;
            }

            if ($this->cartRepository->hasPrompt($user->id, $prompt->id)) {
                continue;
            }

            $price = $prompt->isPayWhatYouWant()
                ? (float) ($carts[$prompt->id] ?? 0)
                : $prompt->price;

            // Drop pay-what-you-want rows whose stored price violates the
            // prompt's minimum — the session data is not trusted
            if ($price < (float) ($prompt->min_price ?? 0)) {
                continue;
            }

            $this->cartRepository->create([
                'user_id' => $user->id,
                'prompt_id' => $prompt->id,
                'price' => $price,
            ]);
        }

        $session->forget('guest_cart');
    }
}
