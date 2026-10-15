<?php

namespace Tests\Feature\Admin;

use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_list_filters_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        User::factory()->create(['role' => 'buyer']);

        $this->actingAs($admin)->get('/admin/users?role=seller')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($seller) {
                $page->component('admin/users/index')
                    ->has('users.data', 1)
                    ->where('users.data.0.id', $seller->id);
            });
    }

    public function test_admin_user_list_filters_by_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $needle = User::factory()->create(['email' => 'unique.email@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($admin)->get('/admin/users?search=unique.email')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($needle) {
                $page->has('users.data', 1)
                    ->where('users.data.0.id', $needle->id);
            });
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.users.update-role', $admin->id), [
            'role' => 'buyer',
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_change_another_users_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);

        $this->actingAs($admin)->put(route('admin.users.update-role', $seller->id), [
            'role' => 'buyer',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $seller->id,
            'role' => 'buyer',
        ]);
    }
}
