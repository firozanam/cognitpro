<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display list of users.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'role']);
        $users = $this->userService->getPaginatedUsers(15);

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:buyer,seller,admin'],
        ]);

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', 'User role updated successfully.');
    }

    /**
     * Ban a user.
     */
    public function ban(int $id): RedirectResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        // Prevent banning admins
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot ban admin users.');
        }

        $user->update(['banned_at' => now()]);

        return back()->with('success', 'User banned successfully.');
    }

    /**
     * Unban a user.
     */
    public function unban(int $id): RedirectResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        $user->update(['banned_at' => null]);

        return back()->with('success', 'User unbanned successfully.');
    }
}
