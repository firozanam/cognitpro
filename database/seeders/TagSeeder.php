<?php

namespace Database\Seeders;

use App\Modules\Prompt\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // General AI tags
            'chatgpt', 'gpt-4', 'gpt-3.5', 'claude', 'midjourney', 'dall-e',
            'stable-diffusion', 'gemini', 'llm', 'ai-art',

            // Content types
            'creative-writing', 'blog', 'marketing', 'seo', 'copywriting',
            'email', 'social-media', 'storytelling', 'script',

            // Technical
            'code-generation', 'debugging', 'documentation', 'api',
            'sql', 'python', 'javascript', 'react', 'laravel',

            // Business
            'business', 'startup', 'entrepreneur', 'sales', 'hr',
            'customer-service', 'productivity', 'automation',

            // Creative
            'art', 'design', 'photography', 'illustration', 'logo',
            'branding', 'ui-ux', '3d', 'animation',

            // Education
            'education', 'tutorial', 'learning', 'explanation',
            'study-guide', 'research', 'academic',

            // Style/Quality
            'professional', 'casual', 'formal', 'creative', 'detailed',
            'concise', 'beginner-friendly', 'advanced',

            // Use case
            'personal', 'enterprise', 'freelance',
            'content-creator', 'developer', 'marketer', 'writer',
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate([
                'name' => $tag,
            ], [
                'slug' => $tag,
            ]);
        }

        $this->command->info('Created '.count($tags).' tags');
    }
}
