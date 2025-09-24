<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Category::with(['children', 'parent'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = Category::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category->load(['parent', 'children']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $category->load(['parent', 'children', 'publishedPrompts']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->load(['parent', 'children']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has children or prompts
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with child categories',
            ], 422);
        }

        if ($category->prompts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated prompts',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Get prompts in a specific category.
     */
    public function prompts(Category $category, Request $request): JsonResponse
    {
        $query = $category->prompts()
            ->with(['user', 'category', 'tags'])
            ->published();

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'popularity') {
            $query->withCount('purchases')->orderBy('purchases_count', $sortOrder);
        } elseif ($sortBy === 'rating') {
            $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $prompts = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $prompts,
        ]);
    }
}
