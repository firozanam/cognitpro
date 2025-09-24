<?php

namespace Database\Factories;

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
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
            'price_paid' => $this->faker->randomFloat(2, 1, 100),
            'payment_gateway' => $this->faker->randomElement(['stripe', 'paypal', 'free']),
            'transaction_id' => $this->faker->uuid(),
            'metadata' => [
                'payment_method' => $this->faker->randomElement(['card', 'paypal', 'bank_transfer']),
                'currency' => 'USD',
            ],
            'purchased_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
