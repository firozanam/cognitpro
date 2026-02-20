<?php

namespace App\Modules\Prompt\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the cart.
     */
    public function index(Request $request): Response
    {
        $summary = $this->cartService->getCartSummary($request->user()->id);

        return Inertia::render('cart/index', [
            'cartItems' => $summary['items']->map(function ($item) {
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
        try {
            $this->cartService->addToCart($request->user(), $prompt);

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
            $this->cartService->removeCartItem($request->user()->id, $cartId);

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
            $this->cartService->clearCart($request->user()->id);

            return back()->with('success', 'Cart cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
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
