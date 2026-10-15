<?php

namespace Tests\Feature\Auth;

use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_banned_user_is_blocked_from_dashboard(): void
    {
        $user = User::factory()->create([
            'banned_at' => now(),
        ]);

        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_banned_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'banned_at' => now(),
        ]);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_active_user_can_still_log_in(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
    }
}
