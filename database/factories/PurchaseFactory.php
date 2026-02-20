<?php

namespace Database\Factories;

use App\Modules\Prompt\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Prompt\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Purchase::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 1, 99);
        $platformFee = round($price * 0.15, 2); // 15% platform fee
        $sellerEarnings = round($price - $platformFee, 2);

        return [
            'order_number' => 'ORD-'.strtoupper(fake()->bothify('??###??##')),
            'price' => $price,
            'platform_fee' => $platformFee,
            'seller_earnings' => $sellerEarnings,
            'payment_method' => fake()->randomElement(['stripe', 'paypal']),
            'payment_id' => 'pi_'.fake()->uuid(),
            'status' => 'completed',
            'purchased_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'refunded_at' => null,
        ];
    }

    /**
     * Indicate that the purchase is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'purchased_at' => null,
        ]);
    }

    /**
     * Indicate that the purchase was refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'refunded_at' => fake()->dateTimeBetween($attributes['purchased_at'] ?? '-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the purchase is disputed.
     */
    public function disputed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disputed',
        ]);
    }

    /**
     * Set a specific price.
     */
    public function withPrice(float $price): static
    {
        $platformFee = round($price * 0.15, 2);
        $sellerEarnings = round($price - $platformFee, 2);

        return $this->state(fn (array $attributes) => [
            'price' => $price,
            'platform_fee' => $platformFee,
            'seller_earnings' => $sellerEarnings,
        ]);
    }
}
