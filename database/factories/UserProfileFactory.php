<?php

namespace Database\Factories;

use App\Modules\User\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\User\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bio' => fake()->optional(0.7)->paragraph(),
            'avatar' => null,
            'website' => fake()->optional(0.3)->url(),
            'social_links' => $this->generateSocialLinks(),
            'seller_verified' => false,
            'total_earnings' => 0,
            'total_sales' => 0,
            'payout_method' => null,
            'payout_details' => null,
        ];
    }

    /**
     * Indicate that the profile is for a verified seller.
     */
    public function verifiedSeller(): static
    {
        return $this->state(fn (array $attributes) => [
            'seller_verified' => true,
            'total_earnings' => fake()->randomFloat(2, 100, 10000),
            'total_sales' => fake()->numberBetween(10, 500),
            'payout_method' => fake()->randomElement(['stripe', 'paypal', 'payoneer']),
        ]);
    }

    /**
     * Indicate that the profile has an avatar.
     */
    public function withAvatar(): static
    {
        return $this->state(fn (array $attributes) => [
            'avatar' => 'avatars/'.fake()->uuid().'.jpg',
        ]);
    }

    /**
     * Generate social links.
     */
    protected function generateSocialLinks(): array
    {
        $links = [];
        $platforms = ['twitter', 'linkedin', 'github', 'youtube'];

        foreach ($platforms as $platform) {
            if (fake()->boolean(30)) {
                $links[$platform] = fake()->url();
            }
        }

        return $links;
    }
}
