<?php

namespace Tests\Feature\Routing;

use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class FlashSharedPropsTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_messages_are_shared_with_inertia_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['success' => 'Prompt approved.'])
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->where('flash.success', 'Prompt approved.')
                    ->where('flash.error', null)
                    ->where('flash.info', null);
            });

        $this->actingAs($user)
            ->withSession(['error' => 'Something went wrong.'])
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->where('flash.error', 'Something went wrong.');
            });
    }
}
