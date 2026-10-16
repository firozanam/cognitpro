<?php

namespace App\Http\Middleware;

use App\Modules\Prompt\Services\CartService;
use App\Modules\Prompt\Services\GuestCartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        protected CartService $cartService,
        protected GuestCartService $guestCartService,
    ) {}

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'avatar' => $request->user()->avatar ?? null,
                    'email_verified_at' => $request->user()->email_verified_at?->toISOString(),
                    'two_factor_enabled' => $request->user()->two_factor_enabled ?? false,
                    'created_at' => $request->user()->created_at->toISOString(),
                    'updated_at' => $request->user()->updated_at->toISOString(),
                ] : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'cartCount' => $request->user()
                ? $this->cartService->getValidCartCount($request->user()->id)
                : $this->guestCartService->getValidCount($request->session()),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'info' => $request->session()->get('info'),
            ],
        ];
    }
}
