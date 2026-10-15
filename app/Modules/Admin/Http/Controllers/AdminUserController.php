<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'in:buyer,seller,admin'],
        ]);

        $filters = array_filter($validated, fn ($value) => $value !== null && $value !== '');
        $users = $this->userService->getPaginatedUsers(15, $filters);

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

        if (! $user) {
            return back()->with('error', 'User not found.');
        }

        try {
            $this->userService->updateUserRole($request->user(), $user, $request->role);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'User role updated successfully.');
    }

    /**
     * Ban a user.
     */
    public function ban(int $id): RedirectResponse
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
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

        if (! $user) {
            return back()->with('error', 'User not found.');
        }

        $user->update(['banned_at' => null]);

        return back()->with('success', 'User unbanned successfully.');
    }
}
