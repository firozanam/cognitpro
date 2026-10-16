<?php

namespace App\Modules\Prompt\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prompt\Models\Cart;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\CartService;
use App\Modules\Prompt\Services\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    protected CartService $cartService;

    protected GuestCartService $guestCartService;

    public function __construct(CartService $cartService, GuestCartService $guestCartService)
    {
        $this->cartService = $cartService;
        $this->guestCartService = $guestCartService;
    }

    /**
     * Display the cart. Authenticated users read the database cart,
     * guests read the session cart.
     */
    public function index(Request $request): Response
    {
        $summary = $request->user()
            ? $this->cartService->getCartSummary($request->user()->id)
            : $this->guestCartService->getSummary($request->session());

        return Inertia::render('cart/index', [
            'cartItems' => $summary['items']->map(function (Cart $item) {
                return [
                    'id' => $item->id,
                    'price' => $item->price,
                    'prompt' => [
                        'id' => $item->prompt->id,
                        'title' => $item->prompt->title,
                        'slug' => $item->prompt->slug,
                        'description' => $item->prompt->description,
                        'ai_model' => $item->prompt->ai_model,
                        'price' => $item->prompt->price,
                        'pricing_model' => $item->prompt->pricing_model,
                        'rating' => $item->prompt->rating,
                        'rating_count' => $item->prompt->rating_count,
                        'seller' => [
                            'id' => $item->prompt->seller->id,
                            'name' => $item->prompt->seller->name,
                        ],
                        'category' => [
                            'id' => $item->prompt->category->id,
                            'name' => $item->prompt->category->name,
                        ],
                    ],
                ];
            }),
            'total' => $summary['total'],
            'count' => $summary['count'],
        ]);
    }

    /**
     * Add a prompt to the cart.
     */
    public function add(Request $request, Prompt $prompt): RedirectResponse
    {
        $request->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            if ($user = $request->user()) {
                $this->cartService->addToCart($user, $prompt, $request->float('price'));
            } else {
                $this->guestCartService->add($request->session(), $prompt, $request->float('price'));
            }

            return back()->with('success', 'Added to cart successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a prompt from the cart.
     */
    public function remove(Request $request, int $cartId): RedirectResponse
    {
        try {
            if ($request->user()) {
                $this->cartService->removeCartItem($request->user()->id, $cartId);
            } else {
                // Guest cart rows are identified by prompt id
                $this->guestCartService->remove($request->session(), $cartId);
            }

            return back()->with('success', 'Item removed from cart.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(Request $request): RedirectResponse
    {
        try {
            if ($request->user()) {
                $this->cartService->clearCart($request->user()->id);
            } else {
                $this->guestCartService->clear($request->session());
            }

            return back()->with('success', 'Cart cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check out the cart: free items complete immediately, paid items
     * continue to the Stripe payment page (STM-01).
     */
    public function checkout(Request $request): RedirectResponse
    {
        try {
            [, $pending] = $this->cartService->checkout($request->user());
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        if ($pending->isNotEmpty()) {
            return redirect()->route('payment.stripe.show', $pending->first()->id)
                ->with('info', 'Checkout started — complete payment for your paid items.');
        }

        return redirect()->route('purchases.index')->with('success', 'Checkout complete!');
    }

    /**
     * Get cart count for header.
     */
    public function count(Request $request): array
    {
        return [
            'count' => $this->cartService->getCartCount($request->user()->id),
        ];
    }
}
