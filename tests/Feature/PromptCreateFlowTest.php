<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Prompt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_prompt_creation_flow()
    {
        // Create test data
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true
        ]);
        
        $tag1 = Tag::create(['name' => 'Test Tag 1', 'slug' => 'test-tag-1']);
        $tag2 = Tag::create(['name' => 'Test Tag 2', 'slug' => 'test-tag-2']);
        
        $seller = User::factory()->create(['role' => 'seller']);

        // Test GET /prompts/create
        $response = $this->actingAs($seller)->get('/prompts/create');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('prompts/create')
                ->has('categories')
                ->has('tags')
        );

        // Test POST /prompts (store)
        $promptData = [
            'title' => 'Test Prompt',
            'description' => 'This is a test prompt description',
            'content' => 'This is the test prompt content',
            'category_id' => $category->id,
            'price_type' => 'fixed',
            'price' => 9.99,
            'tags' => [$tag1->id, $tag2->id],
            'status' => 'draft'
        ];

        $response = $this->actingAs($seller)->post('/prompts', $promptData);
        
        $response->assertRedirect(route('prompts.manage'));
        $response->assertSessionHas('success', 'Prompt created successfully!');

        // Verify prompt was created in database
        $this->assertDatabaseHas('prompts', [
            'title' => 'Test Prompt',
            'description' => 'This is a test prompt description',
            'content' => 'This is the test prompt content',
            'category_id' => $category->id,
            'price_type' => 'fixed',
            'price' => 9.99,
            'user_id' => $seller->id,
            'status' => 'draft'
        ]);

        // Verify tags were attached
        $prompt = Prompt::where('title', 'Test Prompt')->first();
        $this->assertCount(2, $prompt->tags);
        $this->assertTrue($prompt->tags->contains($tag1));
        $this->assertTrue($prompt->tags->contains($tag2));
    }

    public function test_prompt_creation_with_free_pricing()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true
        ]);
        
        $seller = User::factory()->create(['role' => 'seller']);

        $promptData = [
            'title' => 'Free Test Prompt',
            'description' => 'This is a free test prompt',
            'content' => 'Free prompt content',
            'category_id' => $category->id,
            'price_type' => 'free',
            'price' => null,
            'status' => 'published'
        ];

        $response = $this->actingAs($seller)->post('/prompts', $promptData);
        
        $response->assertRedirect(route('prompts.manage'));

        $this->assertDatabaseHas('prompts', [
            'title' => 'Free Test Prompt',
            'price_type' => 'free',
            'price' => null,
            'status' => 'published'
        ]);
    }

    public function test_prompt_creation_with_pay_what_you_want_pricing()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true
        ]);
        
        $seller = User::factory()->create(['role' => 'seller']);

        $promptData = [
            'title' => 'PWYW Test Prompt',
            'description' => 'This is a pay-what-you-want test prompt',
            'content' => 'PWYW prompt content',
            'category_id' => $category->id,
            'price_type' => 'pay_what_you_want',
            'minimum_price' => 1.00,
            'status' => 'draft'
        ];

        $response = $this->actingAs($seller)->post('/prompts', $promptData);
        
        $response->assertRedirect(route('prompts.manage'));

        $this->assertDatabaseHas('prompts', [
            'title' => 'PWYW Test Prompt',
            'price_type' => 'pay_what_you_want',
            'minimum_price' => 1.00
        ]);
    }
}
