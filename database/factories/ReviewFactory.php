<?php

namespace Database\Factories;

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'prompt_id' => Prompt::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence(4),
            'review_text' => $this->faker->paragraphs(2, true),
            'verified_purchase' => $this->faker->boolean(80), // 80% chance of verified purchase
            'is_approved' => $this->faker->boolean(90), // 90% chance of being approved
            'helpful_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}
