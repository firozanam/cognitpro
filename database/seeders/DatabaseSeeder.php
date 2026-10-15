<?php

namespace Database\Seeders;

use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The demo dataset ships known credentials, so it never runs in
     * production (SEC-08). For a clean slate use
     * `php artisan migrate:fresh --seed` — plain `db:seed` appends.
     *
     * Order matters due to foreign keys:
     * 1. UsersTableSeeder - users (admins, sellers, buyers)
     * 2. CategorySeeder / TagSeeder - no dependencies
     * 3. MarketplaceSeeder - prompts, purchases, reviews
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('Demo seeding refused: known credentials must not exist in production.');

            return;
        }

        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // 1. Seed users first (required for foreign keys)
        $this->command->info('=== Seeding Users ===');
        $this->call(UsersTableSeeder::class);
        $this->command->newLine();

        // 2. Seed categories and tags (no dependencies)
        $this->command->info('=== Seeding Categories ===');
        $this->call(CategorySeeder::class);
        $this->command->newLine();

        $this->command->info('=== Seeding Tags ===');
        $this->call(TagSeeder::class);
        $this->command->newLine();

        // 3. Seed marketplace data (prompts, purchases, reviews)
        $this->command->info('=== Seeding Marketplace ===');
        $this->call(MarketplaceSeeder::class);
        $this->command->newLine();

        // Summary
        $this->command->info('=== Seeding Complete ===');
        $this->command->info('Summary:');
        $this->command->table(
            ['Item', 'Count'],
            [
                ['Admin Users', User::where('role', 'admin')->count()],
                ['Seller Users', User::where('role', 'seller')->count()],
                ['Buyer Users', User::where('role', 'buyer')->count()],
                ['Total Users', User::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('Test Accounts (local only — override via SEED_*_PASSWORD):');
        $this->command->info('  Admin: admin@cognitpro.com / '.env('SEED_ADMIN_PASSWORD', 'password'));
        $this->command->info('  Seller: seller@test.com / '.env('SEED_SELLER_PASSWORD', 'password'));
        $this->command->info('  Buyer: buyer@test.com / '.env('SEED_BUYER_PASSWORD', 'password'));
    }
}
