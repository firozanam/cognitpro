<?php

namespace Database\Seeders;

use App\Modules\Prompt\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Writing & Content',
                'description' => 'Prompts for content creation, copywriting, and writing assistance',
                'icon' => 'pen-tool',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Blog Posts', 'description' => 'Blog post generation and ideas'],
                    ['name' => 'Marketing Copy', 'description' => 'Marketing and advertising copy'],
                    ['name' => 'Email Templates', 'description' => 'Professional email templates'],
                    ['name' => 'Social Media', 'description' => 'Social media content creation'],
                    ['name' => 'Storytelling', 'description' => 'Creative writing and stories'],
                ],
            ],
            [
                'name' => 'Business',
                'description' => 'Business-related prompts for productivity and strategy',
                'icon' => 'briefcase',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Business Strategy', 'description' => 'Strategic planning and analysis'],
                    ['name' => 'Sales & Marketing', 'description' => 'Sales scripts and marketing strategies'],
                    ['name' => 'Customer Service', 'description' => 'Customer support templates'],
                    ['name' => 'HR & Recruiting', 'description' => 'Human resources and hiring'],
                ],
            ],
            [
                'name' => 'Programming',
                'description' => 'Code generation, debugging, and technical prompts',
                'icon' => 'code',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Code Generation', 'description' => 'Generate code in various languages'],
                    ['name' => 'Code Review', 'description' => 'Code review and optimization'],
                    ['name' => 'Debugging', 'description' => 'Find and fix bugs'],
                    ['name' => 'Documentation', 'description' => 'Technical documentation'],
                    ['name' => 'API Development', 'description' => 'API design and development'],
                ],
            ],
            [
                'name' => 'Image Generation',
                'description' => 'Prompts for AI image generators like Midjourney and DALL-E',
                'icon' => 'image',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Midjourney', 'description' => 'Midjourney-specific prompts'],
                    ['name' => 'DALL-E', 'description' => 'DALL-E specific prompts'],
                    ['name' => 'Stable Diffusion', 'description' => 'Stable Diffusion prompts'],
                    ['name' => 'Art Styles', 'description' => 'Various art style prompts'],
                    ['name' => 'Photo Realistic', 'description' => 'Photorealistic image prompts'],
                ],
            ],
            [
                'name' => 'Education',
                'description' => 'Learning materials, tutorials, and educational content',
                'icon' => 'graduation-cap',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Tutorials', 'description' => 'Step-by-step tutorials'],
                    ['name' => 'Explanations', 'description' => 'Complex topic explanations'],
                    ['name' => 'Study Guides', 'description' => 'Educational study materials'],
                    ['name' => 'Language Learning', 'description' => 'Language learning prompts'],
                ],
            ],
            [
                'name' => 'Creative',
                'description' => 'Creative prompts for art, music, and entertainment',
                'icon' => 'palette',
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Poetry', 'description' => 'Poetry generation'],
                    ['name' => 'Song Lyrics', 'description' => 'Song and lyric writing'],
                    ['name' => 'Game Design', 'description' => 'Game concepts and mechanics'],
                    ['name' => 'Character Creation', 'description' => 'Character development'],
                ],
            ],
            [
                'name' => 'Productivity',
                'description' => 'Personal productivity and organization prompts',
                'icon' => 'zap',
                'sort_order' => 7,
                'children' => [
                    ['name' => 'Task Management', 'description' => 'Task organization and planning'],
                    ['name' => 'Note Taking', 'description' => 'Note-taking templates'],
                    ['name' => 'Research', 'description' => 'Research assistance'],
                    ['name' => 'Summarization', 'description' => 'Content summarization'],
                ],
            ],
            [
                'name' => 'Data Analysis',
                'description' => 'Data processing, analysis, and visualization prompts',
                'icon' => 'bar-chart',
                'sort_order' => 8,
                'children' => [
                    ['name' => 'Data Interpretation', 'description' => 'Data analysis and insights'],
                    ['name' => 'Report Generation', 'description' => 'Automated report creation'],
                    ['name' => 'Spreadsheet', 'description' => 'Spreadsheet formulas and analysis'],
                    ['name' => 'SQL Queries', 'description' => 'SQL query generation'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = Category::create([
                ...$categoryData,
                'slug' => Str::slug($categoryData['name']),
                'is_active' => true,
            ]);

            foreach ($children as $childData) {
                Category::create([
                    ...$childData,
                    'parent_id' => $category->id,
                    'slug' => Str::slug($childData['name']),
                    'is_active' => true,
                ]);
            }
        }
    }
}
