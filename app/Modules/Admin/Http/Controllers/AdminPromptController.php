<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prompt\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminPromptController extends Controller
{
    protected PromptService $promptService;

    public function __construct(PromptService $promptService)
    {
        $this->promptService = $promptService;
    }

    /**
     * Display prompts for moderation.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'search']);
        
        // Default to showing all prompts if no status filter
        if (!isset($filters['status'])) {
            $filters['status'] = null; // Show all
        }
        
        $prompts = $this->promptService->getPrompts(15, $filters);

        return Inertia::render('admin/prompts/index', [
            'prompts' => $prompts,
            'filters' => $filters,
        ]);
    }

    /**
     * Approve a prompt.
     */
    public function approve(int $id): RedirectResponse
    {
        $prompt = $this->promptService->getPromptById($id);

        if (!$prompt) {
            return back()->with('error', 'Prompt not found.');
        }

        $this->promptService->approvePrompt($prompt);

        return back()->with('success', 'Prompt approved successfully.');
    }

    /**
     * Reject a prompt.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $prompt = $this->promptService->getPromptById($id);

        if (!$prompt) {
            return back()->with('error', 'Prompt not found.');
        }

        $this->promptService->rejectPrompt($prompt, $request->reason);

        return back()->with('success', 'Prompt rejected.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(int $id): RedirectResponse
    {
        $prompt = $this->promptService->getPromptById($id);

        if (!$prompt) {
            return back()->with('error', 'Prompt not found.');
        }

        $this->promptService->toggleFeatured($prompt);

        $status = $prompt->featured ? 'unfeatured' : 'featured';
        return back()->with('success', "Prompt {$status} successfully.");
    }
}
