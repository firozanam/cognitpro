<?php

namespace Database\Factories;

use App\Modules\Prompt\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Prompt\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasSellerResponse = fake()->boolean(30);

        return [
            'rating' => fake()->numberBetween(3, 5), // Bias towards positive reviews
            'title' => fake()->optional(0.6)->sentence(),
            'content' => fake()->optional(0.8)->paragraphs(fake()->numberBetween(1, 3), true),
            'helpful_count' => fake()->numberBetween(0, 50),
            'seller_response' => $hasSellerResponse ? fake()->paragraph() : null,
            'seller_responded_at' => $hasSellerResponse ? fake()->dateTimeBetween('-3 months', 'now') : null,
        ];
    }

    /**
     * Indicate that the review is a 5-star review.
     */
    public function fiveStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'title' => fake()->randomElement([
                'Excellent prompt!',
                'Exactly what I needed',
                'Highly recommended',
                'Works perfectly',
                'Amazing quality',
            ]),
        ]);
    }

    /**
     * Indicate that the review is a 1-star review.
     */
    public function oneStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 1,
            'title' => fake()->randomElement([
                'Not as described',
                'Disappointed',
                'Did not work for me',
                'Poor quality',
            ]),
        ]);
    }

    /**
     * Indicate that the review has a seller response.
     */
    public function withSellerResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'seller_response' => fake()->paragraph(),
            'seller_responded_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    /**
     * Indicate that the review is brief (no content).
     */
    public function brief(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => null,
            'content' => null,
        ]);
    }

    /**
     * Set a specific rating.
     */
    public function withRating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => max(1, min(5, $rating)),
        ]);
    }
}
