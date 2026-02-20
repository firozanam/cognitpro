<?php

namespace Database\Seeders;

use App\Modules\User\Models\User;
use App\Modules\User\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@cognitpro.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        UserProfile::factory()->verifiedSeller()->create([
            'user_id' => $admin->id,
            'bio' => 'Platform administrator with extensive experience in AI and prompt engineering.',
        ]);

        $this->command->info('Created admin user: admin@cognitpro.com');

        // Create verified seller users
        $verifiedSellers = User::factory()->seller()->count(5)->create();
        foreach ($verifiedSellers as $seller) {
            UserProfile::factory()->verifiedSeller()->create([
                'user_id' => $seller->id,
            ]);
        }

        $this->command->info('Created 5 verified sellers');

        // Create regular seller users
        $sellers = User::factory()->seller()->count(10)->create();
        foreach ($sellers as $seller) {
            UserProfile::factory()->create([
                'user_id' => $seller->id,
            ]);
        }

        $this->command->info('Created 10 regular sellers');

        // Create buyer users
        $buyers = User::factory()->buyer()->count(25)->create();
        foreach ($buyers as $buyer) {
            UserProfile::factory()->create([
                'user_id' => $buyer->id,
            ]);
        }

        $this->command->info('Created 25 buyers');

        // Create some unverified users
        $unverifiedUsers = User::factory()->buyer()->unverified()->count(5)->create();
        foreach ($unverifiedUsers as $user) {
            UserProfile::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        $this->command->info('Created 5 unverified users');

        // Create test users for each role
        $testSeller = User::factory()->seller()->create([
            'name' => 'Test Seller',
            'email' => 'seller@test.com',
            'password' => Hash::make('password'),
        ]);
        UserProfile::factory()->verifiedSeller()->create([
            'user_id' => $testSeller->id,
        ]);

        $testBuyer = User::factory()->buyer()->create([
            'name' => 'Test Buyer',
            'email' => 'buyer@test.com',
            'password' => Hash::make('password'),
        ]);
        UserProfile::factory()->create([
            'user_id' => $testBuyer->id,
        ]);

        $this->command->info('Created test users:');
        $this->command->info('  - seller@test.com (verified seller)');
        $this->command->info('  - buyer@test.com (buyer)');
    }
}
