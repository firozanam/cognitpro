<?php

namespace App\Listeners;

use App\Modules\Prompt\Services\GuestCartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCart
{
    protected GuestCartService $guestCartService;

    public function __construct(GuestCartService $guestCartService)
    {
        $this->guestCartService = $guestCartService;
    }

    /**
     * Move the guest session cart into the user's cart on login.
     * Registration logs the user in through the same Login event.
     */
    public function handle(Login $event): void
    {
        $this->guestCartService->mergeToUser(session()->driver(), $event->user);
    }
}
