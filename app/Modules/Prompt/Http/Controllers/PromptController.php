<?php

namespace App\Modules\Prompt\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prompt\Http\Requests\PromptStoreRequest;
use App\Modules\Prompt\Http\Requests\PromptUpdateRequest;
use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\PromptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromptController extends Controller
{
    public function __construct(
        protected PromptService $promptService
    ) {}

    /**
     * Display a listing of prompts (Marketplace).
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['category_id', 'ai_model', 'pricing_model', 'min_price', 'max_price', 'sort_by', 'sort_order', 'search']);
        
        $prompts = $this->promptService->getPrompts(
            perPage: $request->input('per_page', 15),
            filters: $filters
        );

        $categories = Category::active()->ordered()->get();

        return Inertia::render('marketplace/index', [
            'prompts' => $prompts,
            'categories' => $categories,
            'filters' => $filters,
            'aiModels' => $this->getSupportedAiModels(),
        ]);
    }

    /**
     * Show the form for creating a new prompt.
     */
    public function create(): Response
    {
        $categories = Category::active()->ordered()->get();

        return Inertia::render('prompts/create', [
            'categories' => $categories,
            'aiModels' => $this->getSupportedAiModels(),
        ]);
    }

    /**
     * Store a newly created prompt.
     */
    public function store(PromptStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['seller_id'] = $request->user()->id;
        $data['status'] = 'draft';

        $prompt = $this->promptService->createPrompt($data);

        return redirect()->route('prompts.show', $prompt->slug)
            ->with('success', 'Prompt created successfully.');
    }

    /**
     * Display the specified prompt.
     */
    public function show(string $slug, Request $request): Response
    {
        $prompt = $this->promptService->getPromptBySlug($slug);

        if (!$prompt) {
            abort(404);
        }

        // Only show approved prompts to non-owners
        if (!$prompt->isApproved() && $prompt->seller_id !== $request->user()?->id) {
            abort(404);
        }

        // Record view
        $this->promptService->recordView($prompt);

        // Check if user has purchased this prompt
        $hasPurchased = $request->user()?->purchases()
            ->where('prompt_id', $prompt->id)
            ->completed()
            ->exists();

        // Only show content to owner or purchasers
        $showContent = $hasPurchased || $prompt->seller_id === $request->user()?->id;

        return Inertia::render('prompts/show', [
            'prompt' => $prompt,
            'showContent' => $showContent,
            'hasPurchased' => $hasPurchased,
        ]);
    }

    /**
     * Show the form for editing the specified prompt.
     */
    public function edit(Prompt $prompt): Response
    {
        $this->authorize('update', $prompt);

        $categories = Category::active()->ordered()->get();
        $prompt->load('tags');

        return Inertia::render('prompts/edit', [
            'prompt' => $prompt,
            'categories' => $categories,
            'aiModels' => $this->getSupportedAiModels(),
        ]);
    }

    /**
     * Update the specified prompt.
     */
    public function update(PromptUpdateRequest $request, Prompt $prompt): RedirectResponse
    {
        $this->authorize('update', $prompt);

        $prompt = $this->promptService->updatePrompt($prompt, $request->validated());

        return redirect()->route('prompts.show', $prompt->slug)
            ->with('success', 'Prompt updated successfully.');
    }

    /**
     * Remove the specified prompt.
     */
    public function destroy(Prompt $prompt): RedirectResponse
    {
        $this->authorize('delete', $prompt);

        $this->promptService->deletePrompt($prompt);

        return redirect()->route('seller.prompts')
            ->with('success', 'Prompt deleted successfully.');
    }

    /**
     * Submit prompt for approval.
     */
    public function submitForApproval(Prompt $prompt): RedirectResponse
    {
        $this->authorize('update', $prompt);

        try {
            $this->promptService->submitForApproval($prompt);
            return back()->with('success', 'Prompt submitted for approval.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Search prompts.
     */
    public function search(Request $request): Response
    {
        $term = $request->input('q', '');
        
        $prompts = $this->promptService->searchPrompts(
            term: $term,
            perPage: $request->input('per_page', 15)
        );

        return Inertia::render('marketplace/search', [
            'prompts' => $prompts,
            'searchTerm' => $term,
        ]);
    }

    /**
     * Get featured prompts for homepage.
     */
    public function featured(): array
    {
        return [
            'prompts' => $this->promptService->getFeaturedPrompts(10),
        ];
    }

    /**
     * Get supported AI models.
     */
    protected function getSupportedAiModels(): array
    {
        return [
            ['value' => 'gpt-4', 'label' => 'GPT-4'],
            ['value' => 'gpt-4-turbo', 'label' => 'GPT-4 Turbo'],
            ['value' => 'gpt-3.5-turbo', 'label' => 'GPT-3.5 Turbo'],
            ['value' => 'claude-3-opus', 'label' => 'Claude 3 Opus'],
            ['value' => 'claude-3-sonnet', 'label' => 'Claude 3 Sonnet'],
            ['value' => 'claude-3-haiku', 'label' => 'Claude 3 Haiku'],
            ['value' => 'midjourney', 'label' => 'Midjourney'],
            ['value' => 'dall-e-3', 'label' => 'DALL-E 3'],
            ['value' => 'dall-e-2', 'label' => 'DALL-E 2'],
            ['value' => 'stable-diffusion', 'label' => 'Stable Diffusion'],
            ['value' => 'gemini-pro', 'label' => 'Gemini Pro'],
            ['value' => 'llama-2', 'label' => 'Llama 2'],
            ['value' => 'other', 'label' => 'Other'],
        ];
    }
}
