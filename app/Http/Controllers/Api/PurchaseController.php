<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('buyer');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $purchases = Purchase::with(['prompt.user', 'prompt.category'])
            ->where('user_id', Auth::id())
            ->orderBy('purchased_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $purchases,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt_id' => 'required|exists:prompts,id',
            'price_paid' => 'required|numeric|min:0',
            'payment_gateway' => 'required|string|in:stripe,paypal,paddle',
            'transaction_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $prompt = Prompt::findOrFail($data['prompt_id']);

        // Check if prompt is published
        if (!$prompt->isPublished()) {
            return response()->json([
                'success' => false,
                'message' => 'Prompt is not available for purchase',
            ], 422);
        }

        // Check if user already purchased this prompt
        if ($prompt->isPurchasedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You have already purchased this prompt',
            ], 422);
        }

        // Check if user is trying to buy their own prompt
        if ($prompt->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot purchase your own prompt',
            ], 422);
        }

        // Validate price
        $expectedPrice = $prompt->getEffectivePrice();
        if ($prompt->price_type === 'pay_what_you_want') {
            if ($data['price_paid'] < $prompt->minimum_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price paid is below minimum price',
                ], 422);
            }
        } elseif ($prompt->price_type === 'fixed') {
            if (abs($data['price_paid'] - $expectedPrice) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid price',
                ], 422);
            }
        }

        $data['user_id'] = Auth::id();
        $data['purchased_at'] = now();

        $purchase = Purchase::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Purchase completed successfully',
            'data' => $purchase->load(['prompt.user', 'prompt.category']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase): JsonResponse
    {
        // Check if user owns this purchase
        if ($purchase->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $purchase->load(['prompt.user', 'prompt.category', 'prompt.tags']),
        ]);
    }

    /**
     * Update and destroy methods are not applicable for purchases
     * as they represent completed transactions
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Purchases cannot be updated',
        ], 405);
    }

    public function destroy(string $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Purchases cannot be deleted',
        ], 405);
    }
}
