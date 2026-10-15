<?php

namespace App\Modules\Prompt\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\Prompt\Services\PromptService;
use App\Modules\Prompt\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;

    protected PromptService $promptService;

    public function __construct(PurchaseService $purchaseService, PromptService $promptService)
    {
        $this->purchaseService = $purchaseService;
        $this->promptService = $promptService;
    }

    /**
     * Display a listing of buyer's purchases.
     */
    public function index(Request $request): Response
    {
        $purchases = $this->purchaseService->getBuyerPurchases(
            $request->user()->id,
            $request->input('per_page', 15)
        );

        return Inertia::render('purchases/index', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Display the checkout page for a prompt.
     */
    public function checkout(Request $request, Prompt $prompt): Response|RedirectResponse
    {
        // Check if already purchased
        if ($this->purchaseService->hasPurchased($request->user()->id, $prompt->id)) {
            return redirect()->route('purchases.show', [
                'purchase' => $this->purchaseService->getPurchaseByOrderNumber(
                    Purchase::where('buyer_id', $request->user()->id)
                        ->where('prompt_id', $prompt->id)
                        ->first()->order_number
                )->id,
            ])->with('info', 'You already own this prompt.');
        }

        $prompt->load(['seller', 'seller.profile', 'category', 'tags']);

        return Inertia::render('purchases/checkout', [
            'prompt' => $prompt,
        ]);
    }

    /**
     * Process the purchase.
     */
    public function purchase(Request $request, Prompt $prompt): RedirectResponse
    {
        $request->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $purchase = $this->purchaseService->initiatePurchase(
                $request->user(),
                $prompt,
                $request->input('price')
            );

            // For free prompts, complete immediately
            if ($prompt->isFree()) {
                $this->purchaseService->completePurchase(
                    $purchase,
                    'free',
                    'free_'.$purchase->order_number
                );

                return redirect()->route('purchases.show', $purchase->id)
                    ->with('success', 'Prompt acquired successfully!');
            }

            // For paid prompts, redirect to payment
            // This will be handled by payment integration
            return redirect()->route('purchases.payment', $purchase->id);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the payment page for a pending purchase.
     *
     * Consolidated onto the Stripe Payment Element page — the legacy fake
     * card form is gone (BIZ-01).
     */
    public function payment(Request $request, Purchase $purchase): RedirectResponse
    {
        // Ensure user owns this purchase
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        // Already completed
        if ($purchase->isCompleted()) {
            return redirect()->route('purchases.show', $purchase->id);
        }

        return redirect()->route('payment.stripe.show', $purchase->id);
    }

    /**
     * Display the specified purchase.
     */
    public function show(Request $request, Purchase $purchase): Response
    {
        // Ensure user owns this purchase or is the seller
        $isBuyer = $purchase->buyer_id === $request->user()->id;
        $isSeller = $purchase->prompt->seller_id === $request->user()->id;
        $isAdmin = $request->user()->isAdmin();

        if (! $isBuyer && ! $isSeller && ! $isAdmin) {
            abort(403);
        }

        $purchase->load([
            'prompt',
            'prompt.seller',
            'prompt.seller.profile',
            'prompt.category',
            'prompt.tags',
            'review',
        ]);

        // A completed purchase delivers the prompt body (BIZ-04)
        if ($purchase->isCompleted()) {
            $purchase->prompt->makeVisible('content');
        }

        return Inertia::render('purchases/show', [
            'purchase' => $purchase,
            'can_review' => $isBuyer && ! $purchase->hasReview(),
        ]);
    }

    /**
     * Display sales for the authenticated seller.
     */
    public function sales(Request $request): Response
    {
        $sales = $this->purchaseService->getSellerSales(
            $request->user()->id,
            $request->input('per_page', 15)
        );

        $stats = $this->purchaseService->getSellerStats($request->user()->id);

        return Inertia::render('seller/sales', [
            'sales' => $sales,
            'stats' => $stats,
        ]);
    }

    /**
     * Seller hub: their prompts plus sales stats (FEA-05).
     */
    public function sellerDashboard(Request $request): Response
    {
        $sellerId = $request->user()->id;

        $prompts = $this->promptService->getPromptsBySeller($sellerId, 10);
        $salesStats = $this->purchaseService->getSellerStats($sellerId);

        $promptQuery = Prompt::where('seller_id', $sellerId);

        $stats = [
            'total_earnings' => $salesStats['total_earnings'],
            'total_sales' => $salesStats['total_sales'],
            'total_prompts' => (clone $promptQuery)->count(),
            'total_views' => (clone $promptQuery)->sum('views_count'),
            'average_rating' => (clone $promptQuery)->where('rating_count', '>', 0)->avg('rating') ?? 0,
            'pending_payout' => 0,
        ];

        return Inertia::render('seller/dashboard', [
            'prompts' => $prompts,
            'stats' => $stats,
        ]);
    }
}
