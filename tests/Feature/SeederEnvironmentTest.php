<?php

namespace Tests\Feature;

use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeederEnvironmentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function production_env_refuses_to_seed_known_accounts(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('db:seed', ['--force' => true]);

        $this->assertSame(0, User::count(), 'production seeding must not create any known account');
    }

    #[Test]
    public function seeding_runs_non_interactively_outside_production(): void
    {
        $this->artisan('db:seed');

        $this->assertTrue(User::where('role', 'admin')->exists());
        $this->assertTrue(User::where('email', 'seller@test.com')->exists());
    }

    #[Test]
    public function admin_password_follows_the_environment(): void
    {
        $_SERVER['SEED_ADMIN_PASSWORD'] = 'env-driven-secret';

        try {
            $this->artisan('db:seed');

            $admin = User::where('email', 'admin@cognitpro.com')->first();
            $this->assertNotNull($admin);
            $this->assertTrue(
                Hash::check('env-driven-secret', $admin->password),
                'admin password must come from SEED_ADMIN_PASSWORD when set'
            );
        } finally {
            unset($_SERVER['SEED_ADMIN_PASSWORD']);
        }
    }
}
