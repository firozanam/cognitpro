<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $admin = User::firstOrCreate(
            ['email' => 'admin@cognitpro.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin',
            ]
        );

        $seller1 = User::firstOrCreate(
            ['email' => 'seller1@cognitpro.com'],
            [
                'name' => 'John Seller',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'seller',
            ]
        );

        $seller2 = User::firstOrCreate(
            ['email' => 'seller2@cognitpro.com'],
            [
                'name' => 'Jane Creator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'seller',
            ]
        );

        $buyer1 = User::firstOrCreate(
            ['email' => 'buyer1@cognitpro.com'],
            [
                'name' => 'Mike Buyer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'buyer',
            ]
        );

        $buyer2 = User::firstOrCreate(
            ['email' => 'buyer2@cognitpro.com'],
            [
                'name' => 'Sarah Customer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'buyer',
            ]
        );

        // Create user profiles
        UserProfile::firstOrCreate(
            ['user_id' => $seller1->id],
            [
                'bio' => 'Experienced AI prompt engineer specializing in creative writing and business automation.',
                'website' => 'https://johnseller.com',
                'location' => 'San Francisco, CA',
            ]
        );

        UserProfile::firstOrCreate(
            ['user_id' => $seller2->id],
            [
                'bio' => 'Creative AI enthusiast focused on marketing and content creation prompts.',
                'website' => 'https://janecreator.com',
                'location' => 'New York, NY',
            ]
        );

        // Create categories
        $categories = [
            ['name' => 'Writing & Content', 'color' => '#3B82F6', 'icon' => '✍️', 'description' => 'Prompts for creative writing, copywriting, and content creation'],
            ['name' => 'Business & Marketing', 'color' => '#10B981', 'icon' => '💼', 'description' => 'Business strategy, marketing campaigns, and professional communication'],
            ['name' => 'Code & Development', 'color' => '#8B5CF6', 'icon' => '💻', 'description' => 'Programming, debugging, and software development assistance'],
            ['name' => 'Education & Learning', 'color' => '#F59E0B', 'icon' => '📚', 'description' => 'Educational content, tutorials, and learning materials'],
            ['name' => 'Creative & Design', 'color' => '#EF4444', 'icon' => '🎨', 'description' => 'Creative projects, design concepts, and artistic inspiration'],
            ['name' => 'Data & Analysis', 'color' => '#06B6D4', 'icon' => '📊', 'description' => 'Data analysis, research, and statistical insights'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'slug' => \Illuminate\Support\Str::slug($categoryData['name']),
                    'description' => $categoryData['description'],
                    'color' => $categoryData['color'],
                    'icon' => $categoryData['icon'],
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        }

        // Create tags
        $tags = [
            'ChatGPT', 'GPT-4', 'Claude', 'Copywriting', 'SEO', 'Marketing', 'Social Media',
            'Email', 'Blog', 'Creative Writing', 'Technical Writing', 'Business Plan',
            'Strategy', 'Analysis', 'Research', 'Programming', 'Python', 'JavaScript',
            'Web Development', 'Data Science', 'Machine Learning', 'Education', 'Tutorial',
            'Learning', 'Design', 'Creative', 'Art', 'Brainstorming', 'Productivity'
        ];

        foreach ($tags as $tagName) {
            Tag::firstOrCreate(
                ['name' => $tagName],
                [
                    'slug' => \Illuminate\Support\Str::slug($tagName),
                    'description' => "Prompts related to {$tagName}",
                ]
            );
        }

        // Get created categories and tags
        $writingCategory = Category::where('name', 'Writing & Content')->first();
        $businessCategory = Category::where('name', 'Business & Marketing')->first();
        $codeCategory = Category::where('name', 'Code & Development')->first();
        $educationCategory = Category::where('name', 'Education & Learning')->first();

        $chatgptTag = Tag::where('name', 'ChatGPT')->first();
        $copywritingTag = Tag::where('name', 'Copywriting')->first();
        $seoTag = Tag::where('name', 'SEO')->first();
        $programmingTag = Tag::where('name', 'Programming')->first();

        // Create sample prompts
        $prompts = [
            [
                'user_id' => $seller1->id,
                'category_id' => $writingCategory->id,
                'title' => 'Professional Email Writer Pro',
                'description' => 'A comprehensive prompt for writing professional emails that get results. Perfect for business communication, follow-ups, and networking.',
                'content' => 'You are a professional email writing expert. Write a [TYPE] email for [CONTEXT]. The email should be [TONE] and include [SPECIFIC_REQUIREMENTS]. Make sure to:\n\n1. Use an engaging subject line\n2. Start with appropriate greeting\n3. Clearly state the purpose\n4. Include a clear call-to-action\n5. End with professional closing\n\nEmail type: [TYPE]\nContext: [CONTEXT]\nTone: [TONE]\nSpecific requirements: [SPECIFIC_REQUIREMENTS]',
                'price' => 9.99,
                'price_type' => 'fixed',
                'status' => 'published',
                'featured' => true,
                'published_at' => now()->subDays(5),
                'tags' => [$chatgptTag->id, $copywritingTag->id],
            ],
            [
                'user_id' => $seller2->id,
                'category_id' => $businessCategory->id,
                'title' => 'SEO Content Strategy Generator',
                'description' => 'Generate comprehensive SEO content strategies that drive organic traffic. Includes keyword research, content planning, and optimization techniques.',
                'content' => 'Act as an SEO expert and content strategist. Create a comprehensive SEO content strategy for [BUSINESS/WEBSITE]. Include:\n\n1. Target keyword analysis\n2. Content pillars and topics\n3. Content calendar for 3 months\n4. On-page optimization checklist\n5. Link building opportunities\n\nBusiness/Website: [BUSINESS/WEBSITE]\nTarget audience: [TARGET_AUDIENCE]\nIndustry: [INDUSTRY]\nCompetitors: [COMPETITORS]',
                'price' => 15.99,
                'price_type' => 'fixed',
                'status' => 'published',
                'featured' => true,
                'published_at' => now()->subDays(3),
                'tags' => [$seoTag->id, $copywritingTag->id],
            ],
            [
                'user_id' => $seller1->id,
                'category_id' => $codeCategory->id,
                'title' => 'Code Review Assistant',
                'description' => 'Get detailed code reviews with suggestions for improvement, best practices, and bug detection.',
                'content' => 'You are a senior software engineer conducting a code review. Analyze the following code and provide:\n\n1. Code quality assessment\n2. Potential bugs or issues\n3. Performance improvements\n4. Best practice recommendations\n5. Security considerations\n\nCode to review:\n[CODE]\n\nProgramming language: [LANGUAGE]\nContext/Purpose: [CONTEXT]',
                'price' => 12.99,
                'price_type' => 'fixed',
                'status' => 'published',
                'featured' => false,
                'published_at' => now()->subDays(1),
                'tags' => [$programmingTag->id],
            ],
            [
                'user_id' => $seller2->id,
                'category_id' => $educationCategory->id,
                'title' => 'Interactive Learning Module Creator',
                'description' => 'Create engaging educational content with quizzes, exercises, and interactive elements.',
                'content' => 'Design an interactive learning module about [TOPIC] for [AUDIENCE_LEVEL]. Include:\n\n1. Learning objectives\n2. Core content breakdown\n3. Interactive exercises\n4. Knowledge check questions\n5. Practical applications\n\nTopic: [TOPIC]\nAudience level: [AUDIENCE_LEVEL]\nDuration: [DURATION]\nLearning style: [LEARNING_STYLE]',
                'price' => 0.00,
                'price_type' => 'free',
                'status' => 'published',
                'featured' => false,
                'published_at' => now()->subHours(12),
                'tags' => [],
            ],
        ];

        foreach ($prompts as $promptData) {
            $tags = $promptData['tags'] ?? [];
            unset($promptData['tags']);

            $prompt = Prompt::firstOrCreate(
                ['title' => $promptData['title']],
                $promptData
            );

            if (!empty($tags)) {
                $prompt->tags()->sync($tags);
            }
        }

        // Create sample purchases and reviews
        $prompts = Prompt::all();
        
        foreach ($prompts->take(3) as $prompt) {
            // Create purchases
            $purchase1 = Purchase::firstOrCreate([
                'user_id' => $buyer1->id,
                'prompt_id' => $prompt->id,
            ], [
                'price_paid' => $prompt->price,
                'payment_gateway' => 'stripe',
                'transaction_id' => 'txn_' . \Illuminate\Support\Str::random(16),
                'purchased_at' => now()->subDays(rand(1, 10)),
            ]);

            $purchase2 = Purchase::firstOrCreate([
                'user_id' => $buyer2->id,
                'prompt_id' => $prompt->id,
            ], [
                'price_paid' => $prompt->price,
                'payment_gateway' => 'stripe',
                'transaction_id' => 'txn_' . \Illuminate\Support\Str::random(16),
                'purchased_at' => now()->subDays(rand(1, 10)),
            ]);

            // Create reviews
            Review::firstOrCreate([
                'user_id' => $buyer1->id,
                'prompt_id' => $prompt->id,
            ], [
                'rating' => rand(4, 5),
                'title' => 'Great prompt!',
                'review_text' => 'This prompt worked exactly as described. Very helpful for my project.',
                'is_approved' => true,
                'verified_purchase' => true,
            ]);

            Review::firstOrCreate([
                'user_id' => $buyer2->id,
                'prompt_id' => $prompt->id,
            ], [
                'rating' => rand(3, 5),
                'title' => 'Useful and well-structured',
                'review_text' => 'Good quality prompt with clear instructions. Would recommend.',
                'is_approved' => true,
                'verified_purchase' => true,
            ]);
        }
    }
}
