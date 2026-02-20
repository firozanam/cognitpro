<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\PaymentService;
use App\Modules\Prompt\Models\Purchase;
use App\Modules\Prompt\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected PurchaseService $purchaseService;

    public function __construct(PaymentService $paymentService, PurchaseService $purchaseService)
    {
        $this->paymentService = $paymentService;
        $this->purchaseService = $purchaseService;
    }

    /**
     * Show the payment page for a purchase.
     */
    public function show(Request $request, Purchase $purchase): Response|RedirectResponse
    {
        // Ensure user owns this purchase
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        // Already completed
        if ($purchase->isCompleted()) {
            return redirect()->route('purchases.show', $purchase->id);
        }

        $purchase->load(['prompt', 'prompt.seller']);

        // Create payment intent
        $paymentIntent = null;
        if ($purchase->price > 0) {
            try {
                $paymentIntent = $this->paymentService->createPaymentIntent($purchase);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return Inertia::render('purchases/stripe-payment', [
            'purchase' => $purchase,
            'stripeKey' => $this->paymentService->getPublishableKey(),
            'clientSecret' => $paymentIntent['client_secret'] ?? null,
            'intentId' => $paymentIntent['intent_id'] ?? null,
        ]);
    }

    /**
     * Process the payment confirmation.
     */
    public function confirm(Request $request, Purchase $purchase): JsonResponse|RedirectResponse
    {
        $request->validate([
            'payment_intent_id' => ['required', 'string'],
        ]);

        // Ensure user owns this purchase
        if ($purchase->buyer_id !== $request->user()->id) {
            abort(403);
        }

        try {
            // Verify the payment intent
            $intent = $this->paymentService->confirmPaymentIntent($request->payment_intent_id);

            if ($intent['status'] === 'succeeded') {
                // Complete the purchase
                $this->purchaseService->completePurchase(
                    $purchase,
                    'stripe',
                    $request->payment_intent_id
                );

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'redirect' => route('purchases.show', $purchase->id),
                    ]);
                }

                return redirect()->route('purchases.show', $purchase->id)
                    ->with('success', 'Payment successful! You now have access to this prompt.');
            }

            throw new \Exception('Payment not completed. Please try again.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Handle Stripe webhook.
     */
    public function webhook(Request $request): JsonResponse
    {
        $secret = config('services.stripe.webhook_secret');
        
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        $event = $this->paymentService->verifyWebhookSignature($payload, $signature, $secret);

        if (!$event) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handleSuccessfulPayment($event['data']['object']);
                break;

            case 'payment_intent.payment_failed':
                $this->handleFailedPayment($event['data']['object']);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle successful payment from webhook.
     */
    protected function handleSuccessfulPayment(array $paymentIntent): void
    {
        $purchaseId = $paymentIntent['metadata']['purchase_id'] ?? null;

        if (!$purchaseId) {
            return;
        }

        $purchase = Purchase::find($purchaseId);

        if (!$purchase || $purchase->isCompleted()) {
            return;
        }

        $this->purchaseService->completePurchase(
            $purchase,
            'stripe',
            $paymentIntent['id']
        );
    }

    /**
     * Handle failed payment from webhook.
     */
    protected function handleFailedPayment(array $paymentIntent): void
    {
        $purchaseId = $paymentIntent['metadata']['purchase_id'] ?? null;

        if (!$purchaseId) {
            return;
        }

        $purchase = Purchase::find($purchaseId);

        if (!$purchase) {
            return;
        }

        $this->purchaseService->failPurchase(
            $purchase,
            $paymentIntent['last_payment_error']['message'] ?? 'Payment failed'
        );
    }
}
