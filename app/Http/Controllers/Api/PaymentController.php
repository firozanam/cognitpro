<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Services\PaymentService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private $stripeService;
    private $paymentService;

    public function __construct(StripeService $stripeService, PaymentService $paymentService)
    {
        $this->stripeService = $stripeService;
        $this->paymentService = $paymentService;
    }

    /**
     * Create a payment intent for a prompt purchase.
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt_id' => 'required|exists:prompts,id',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $prompt = Prompt::findOrFail($request->prompt_id);

            // Check if user can buy
            if (!$user->canBuy()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to make purchases',
                ], 403);
            }

            // Check if prompt is published
            if (!$prompt->isPublished()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This prompt is not available for purchase',
                ], 422);
            }

            // Check if user already owns this prompt
            if ($prompt->isPurchasedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already own this prompt',
                ], 422);
            }

            // Check if user is trying to buy their own prompt
            if ($prompt->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot purchase your own prompt',
                ], 422);
            }

            // Validate amount
            $expectedPrice = $prompt->getEffectivePrice();
            if ($prompt->price_type === 'fixed' && abs($request->amount - $expectedPrice) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid amount',
                ], 422);
            }

            if ($prompt->price_type === 'pay_what_you_want' && $request->amount < $prompt->minimum_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount is below minimum price',
                ], 422);
            }

            // Create payment intent
            $paymentIntent = $this->stripeService->createPaymentIntent($user, $prompt, $request->amount);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_intent_id' => $paymentIntent['payment_intent_id'],
                    'client_secret' => $paymentIntent['client_secret'],
                    'amount' => $paymentIntent['amount'],
                    'currency' => $paymentIntent['currency'],
                    'publishable_key' => $this->stripeService->getPublishableKey(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Payment intent creation failed', [
                'user_id' => Auth::id(),
                'prompt_id' => $request->prompt_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent',
            ], 500);
        }
    }

    /**
     * Confirm payment and complete purchase.
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Confirm payment with Stripe
            $paymentResult = $this->stripeService->confirmPayment($request->payment_intent_id);

            if ($paymentResult['status'] !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment was not successful',
                ], 422);
            }

            // Get payment record
            $payment = Payment::where('transaction_id', $request->payment_intent_id)->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found',
                ], 404);
            }

            // Get prompt from payment metadata
            $promptId = $payment->metadata['prompt_id'] ?? null;
            if (!$promptId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment metadata',
                ], 422);
            }

            $prompt = Prompt::find($promptId);
            if (!$prompt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prompt not found',
                ], 404);
            }

            // Create purchase record using PaymentService
            $purchase = $this->paymentService->processPayment(
                Auth::user(),
                $prompt,
                $payment->amount,
                'stripe',
                $request->payment_intent_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed and purchase completed',
                'data' => [
                    'purchase_id' => $purchase->uuid,
                    'prompt' => [
                        'id' => $prompt->id,
                        'uuid' => $prompt->uuid,
                        'title' => $prompt->title,
                    ],
                    'amount_paid' => $purchase->price_paid,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', [
                'user_id' => Auth::id(),
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment',
            ], 500);
        }
    }

    /**
     * Handle Stripe webhooks.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $signature = $request->header('Stripe-Signature');

            if (!$signature) {
                return response()->json(['error' => 'Missing signature'], 400);
            }

            $success = $this->stripeService->handleWebhook($payload, $signature);

            if ($success) {
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['error' => 'Webhook processing failed'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 400);
        }
    }

    /**
     * Get payment history for the authenticated user.
     */
    public function getPaymentHistory(Request $request): JsonResponse
    {
        try {
            $payments = Payment::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $payments,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get payment history', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment history',
            ], 500);
        }
    }

    /**
     * Get a specific payment by ID.
     */
    public function getPayment(Payment $payment): JsonResponse
    {
        // Check if user owns this payment
        if ($payment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }
}
