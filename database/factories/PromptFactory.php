<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prompt>
 */
class PromptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        $description = $this->faker->paragraphs(3, true);

        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'title' => $title,
            'description' => $description,
            'content' => $this->faker->paragraphs(5, true),
            'excerpt' => Str::limit($description, 200),
            'category_id' => Category::factory(),
            'price_type' => $this->faker->randomElement(['free', 'fixed', 'pay_what_you_want']),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'minimum_price' => $this->faker->randomFloat(2, 1, 10),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'featured' => $this->faker->boolean(20), // 20% chance of being featured
            'version' => 1,
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
