<?php

namespace Database\Factories;

use App\Modules\Prompt\Models\Category;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Prompt\Models\Prompt>
 */
class PromptFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Prompt::class;

    /**
     * The AI models available.
     */
    protected array $aiModels = [
        'GPT-4',
        'GPT-3.5',
        'Claude 3 Opus',
        'Claude 3 Sonnet',
        'Midjourney v6',
        'Midjourney v5',
        'DALL-E 3',
        'Stable Diffusion XL',
        'Gemini Pro',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $pricingModel = fake()->randomElement(['fixed', 'pay_what_you_want', 'free']);
        $price = $pricingModel === 'free' ? 0 : fake()->randomFloat(2, 1, 99);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(2, true),
            'content' => $this->generatePromptContent(),
            'ai_model' => fake()->randomElement($this->aiModels),
            'price' => $price,
            'pricing_model' => $pricingModel,
            'min_price' => $pricingModel === 'pay_what_you_want' ? fake()->randomFloat(2, 1, 5) : null,
            'version' => 1,
            'status' => 'approved',
            'featured' => fake()->boolean(10),
            'views_count' => fake()->numberBetween(0, 1000),
            'purchases_count' => fake()->numberBetween(0, 100),
            'rating' => fake()->randomFloat(2, 3, 5),
            'rating_count' => fake()->numberBetween(0, 50),
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the prompt is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the prompt is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the prompt is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the prompt is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
            'rating' => fake()->randomFloat(2, 4.5, 5),
            'purchases_count' => fake()->numberBetween(50, 500),
        ]);
    }

    /**
     * Indicate that the prompt is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
            'pricing_model' => 'free',
            'min_price' => null,
        ]);
    }

    /**
     * Indicate that the prompt uses pay-what-you-want pricing.
     */
    public function payWhatYouWant(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->randomFloat(2, 5, 20),
            'pricing_model' => 'pay_what_you_want',
            'min_price' => fake()->randomFloat(2, 1, 5),
        ]);
    }

    /**
     * Set a specific AI model.
     */
    public function forModel(string $model): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_model' => $model,
        ]);
    }

    /**
     * Generate realistic prompt content.
     */
    protected function generatePromptContent(): string
    {
        $templates = [
            "You are an expert {role}. Your task is to {task}. Follow these guidelines:\n\n1. {guideline1}\n2. {guideline2}\n3. {guideline3}\n\nOutput format: {format}",
            "Act as a {role} with {experience} years of experience. Help me {task}.\n\nContext: {context}\n\nRequirements:\n- {req1}\n- {req2}\n- {req3}",
            "I need you to {task}. Here's the background:\n\n{background}\n\nPlease provide {output} that includes:\n- {item1}\n- {item2}\n- {item3}",
        ];

        $template = fake()->randomElement($templates);

        $replacements = [
            '{role}' => fake()->randomElement(['software engineer', 'marketing strategist', 'content writer', 'data analyst', 'product manager']),
            '{task}' => fake()->sentence(),
            '{guideline1}' => fake()->sentence(),
            '{guideline2}' => fake()->sentence(),
            '{guideline3}' => fake()->sentence(),
            '{format}' => fake()->randomElement(['JSON', 'Markdown', 'Plain text', 'HTML']),
            '{experience}' => fake()->numberBetween(5, 20),
            '{context}' => fake()->paragraph(),
            '{req1}' => fake()->sentence(),
            '{req2}' => fake()->sentence(),
            '{req3}' => fake()->sentence(),
            '{background}' => fake()->paragraph(),
            '{output}' => fake()->randomElement(['a detailed response', 'an analysis', 'a comprehensive guide', 'a solution']),
            '{item1}' => fake()->sentence(),
            '{item2}' => fake()->sentence(),
            '{item3}' => fake()->sentence(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
