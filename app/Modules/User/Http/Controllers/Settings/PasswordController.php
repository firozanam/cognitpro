<?php

namespace App\Modules\User\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\Settings\PasswordUpdateRequest;
use App\Modules\User\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/password');
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $this->userService->updatePassword(
            $request->user(),
            $request->password
        );

        return back();
    }
}
