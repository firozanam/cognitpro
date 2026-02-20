<?php

namespace Database\Seeders;

use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The order of seeders is important due to foreign key relationships:
     * 1. UsersTableSeeder - Creates users (admins, sellers, buyers)
     * 2. CategorySeeder - Creates categories (no dependencies)
     * 3. TagSeeder - Creates tags (no dependencies)
     * 4. MarketplaceSeeder - Creates prompts, purchases, reviews (depends on users, categories, tags)
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // Check if we should run a fresh seed or just add to existing data
        $fresh = $this->command->confirm('Do you want to fresh migrate before seeding?', false);

        if ($fresh) {
            $this->command->call('migrate:fresh');
            $this->command->newLine();
        }

        // 1. Seed users first (required for foreign keys)
        $this->command->info('=== Seeding Users ===');
        $this->call(UsersTableSeeder::class);
        $this->command->newLine();

        // 2. Seed categories (no dependencies)
        $this->command->info('=== Seeding Categories ===');
        $this->call(CategorySeeder::class);
        $this->command->newLine();

        // 3. Seed tags (no dependencies)
        $this->command->info('=== Seeding Tags ===');
        $this->call(TagSeeder::class);
        $this->command->newLine();

        // 4. Seed marketplace data (prompts, purchases, reviews)
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
        $this->command->info('Test Accounts:');
        $this->command->info('  Admin: admin@cognitpro.com / password');
        $this->command->info('  Seller: seller@test.com / password');
        $this->command->info('  Buyer: buyer@test.com / password');
    }
}
