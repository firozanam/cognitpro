<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prompt\Services\PromptService;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\User\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    protected UserService $userService;
    protected PromptService $promptService;
    protected PurchaseService $purchaseService;

    public function __construct(
        UserService $userService,
        PromptService $promptService,
        PurchaseService $purchaseService
    ) {
        $this->userService = $userService;
        $this->promptService = $promptService;
        $this->purchaseService = $purchaseService;
    }

    /**
     * Display the admin dashboard.
     */
    public function index(Request $request): Response
    {
        // Get platform statistics
        $stats = [
            'total_users' => $this->userService->getTotalUsers(),
            'total_sellers' => $this->userService->getTotalSellers(),
            'total_prompts' => $this->promptService->getTotalPrompts(),
            'pending_prompts' => $this->promptService->getPendingPromptsCount(),
            'total_sales' => $this->purchaseService->getTotalSales(),
            'total_revenue' => $this->purchaseService->getTotalRevenue(),
        ];

        // Get recent data
        $recentUsers = $this->userService->getRecentUsers(5);
        $recentPurchases = $this->purchaseService->getRecentPurchases(5);
        $pendingPrompts = $this->promptService->getPendingPrompts(10);

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentPurchases' => $recentPurchases,
            'pendingPrompts' => $pendingPrompts,
        ]);
    }
}
