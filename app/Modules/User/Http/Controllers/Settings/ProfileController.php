<?php

namespace App\Modules\User\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\Settings\ProfileDeleteRequest;
use App\Modules\User\Http\Requests\Settings\ProfileUpdateRequest;
use App\Modules\User\Services\UserService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->userService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::guard('web')->logout();

        $this->userService->deleteAccount($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
