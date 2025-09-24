<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $buyer;
    protected User $admin;
    protected Category $category;
    protected Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->seller = User::create([
            'name' => 'Test Seller',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'email_verified_at' => now(),
        ]);

        $this->buyer = User::create([
            'name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
            'email_verified_at' => now(),
        ]);

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create test category
        $this->category = Category::create([
            'name' => 'Writing',
            'slug' => 'writing',
            'description' => 'Creative writing prompts',
            'color' => '#3B82F6',
            'icon' => '✍️',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Create test tag
        $this->tag = Tag::create([
            'name' => 'Creative Writing',
            'slug' => 'creative-writing',
            'description' => 'Prompts for creative writing',
        ]);
    }

    public function test_complete_marketplace_workflow()
    {
        // 1. Seller creates a profile
        $profile = UserProfile::create([
            'user_id' => $this->seller->id,
            'bio' => 'Professional AI prompt creator',
            'website' => 'https://example.com',
            'location' => 'San Francisco, CA',
        ]);

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $this->seller->id,
            'bio' => 'Professional AI prompt creator',
        ]);

        // 2. Seller creates a prompt
        $prompt = Prompt::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Creative Writing Assistant',
            'description' => 'A comprehensive prompt for creative writing assistance',
            'content' => 'You are a creative writing assistant...',
            'excerpt' => 'Help with creative writing tasks',
            'price' => 19.99,
            'price_type' => 'fixed',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Attach tags to prompt
        $prompt->tags()->attach($this->tag->id);

        $this->assertDatabaseHas('prompts', [
            'user_id' => $this->seller->id,
            'title' => 'Creative Writing Assistant',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('prompt_tags', [
            'prompt_id' => $prompt->id,
            'tag_id' => $this->tag->id,
        ]);

        // 3. Buyer discovers the prompt (public access)
        $this->assertTrue($prompt->isPublished());
        $this->assertEquals(19.99, $prompt->getEffectivePrice());
        $this->assertFalse($prompt->isPurchasedBy($this->buyer));

        // 4. Buyer purchases the prompt
        $purchase = Purchase::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 19.99,
            'payment_gateway' => 'stripe',
            'transaction_id' => 'txn_integration_test',
            'purchased_at' => now(),
        ]);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 19.99,
        ]);

        // 5. Verify purchase relationship
        $this->assertTrue($prompt->isPurchasedBy($this->buyer));

        // 6. Buyer leaves a review
        $review = Review::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'rating' => 5,
            'title' => 'Excellent prompt!',
            'review_text' => 'This prompt helped me write amazing stories.',
            'is_approved' => true,
            'verified_purchase' => true,
        ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'rating' => 5,
        ]);

        // 7. Verify review relationships and methods
        $this->assertTrue($review->isApproved());
        $this->assertTrue($review->isVerifiedPurchase());
        $this->assertEquals(1, $prompt->reviews->count());
        $this->assertEquals(5, $prompt->reviews->first()->rating);

        // 8. Test category relationships
        $this->assertEquals(1, $this->category->prompts->count());
        $this->assertTrue($this->category->isActive());

        // 9. Test tag relationships
        $this->assertEquals(1, $this->tag->prompts->count());

        // 10. Test user relationships
        $this->assertEquals(1, $this->seller->prompts->count());
        $this->assertEquals(1, $this->buyer->purchases->count());
        $this->assertEquals(1, $this->buyer->reviews->count());

        // 11. Test user role methods
        $this->assertTrue($this->seller->isSeller());
        $this->assertTrue($this->seller->canSell());
        $this->assertTrue($this->buyer->isBuyer());
        $this->assertTrue($this->buyer->canBuy());
        $this->assertTrue($this->admin->isAdmin());

        // 12. Test UUID generation
        $this->assertNotNull($prompt->uuid);
        $this->assertNotNull($purchase->uuid);
        $this->assertNotNull($review->uuid);
    }

    public function test_pay_what_you_want_workflow()
    {
        // Create a pay-what-you-want prompt
        $prompt = Prompt::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Flexible Pricing Prompt',
            'description' => 'A prompt with flexible pricing',
            'content' => 'Flexible content...',
            'excerpt' => 'Flexible excerpt',
            'price' => 10.00,
            'minimum_price' => 2.00,
            'price_type' => 'pay_what_you_want',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Test effective price
        $this->assertEquals(10.00, $prompt->getEffectivePrice());

        // Buyer pays more than suggested price
        $purchase = Purchase::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 25.00, // Buyer chose to pay more
            'payment_gateway' => 'stripe',
            'transaction_id' => 'txn_pwyw_test',
            'purchased_at' => now(),
        ]);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 25.00,
        ]);

        $this->assertTrue($prompt->isPurchasedBy($this->buyer));
    }

    public function test_free_prompt_workflow()
    {
        // Create a free prompt
        $prompt = Prompt::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Free Sample Prompt',
            'description' => 'A free sample prompt',
            'content' => 'Free content...',
            'excerpt' => 'Free excerpt',
            'price' => 0.00,
            'price_type' => 'free',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Test effective price
        $this->assertEquals(0.00, $prompt->getEffectivePrice());

        // Buyer "purchases" free prompt
        $purchase = Purchase::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 0.00,
            'payment_gateway' => 'free',
            'transaction_id' => 'free_download_' . time(),
            'purchased_at' => now(),
        ]);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 0.00,
        ]);

        $this->assertTrue($prompt->isPurchasedBy($this->buyer));
    }

    public function test_multiple_categories_and_tags()
    {
        // Create additional categories
        $category2 = Category::create([
            'name' => 'Business',
            'slug' => 'business',
            'description' => 'Business-related prompts',
            'color' => '#10B981',
            'icon' => '💼',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create additional tags
        $tag2 = Tag::create([
            'name' => 'Marketing',
            'slug' => 'marketing',
            'description' => 'Marketing-related prompts',
        ]);

        $tag3 = Tag::create([
            'name' => 'Strategy',
            'slug' => 'strategy',
            'description' => 'Strategic planning prompts',
        ]);

        // Create prompt with multiple tags
        $prompt = Prompt::create([
            'user_id' => $this->seller->id,
            'category_id' => $category2->id,
            'title' => 'Business Strategy Prompt',
            'description' => 'A comprehensive business strategy prompt',
            'content' => 'Business strategy content...',
            'excerpt' => 'Business strategy excerpt',
            'price' => 29.99,
            'price_type' => 'fixed',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Attach multiple tags
        $prompt->tags()->attach([$tag2->id, $tag3->id]);

        // Test relationships
        $this->assertEquals(1, $category2->prompts->count());
        $this->assertEquals(2, $prompt->tags->count());
        $this->assertEquals(1, $tag2->prompts->count());
        $this->assertEquals(1, $tag3->prompts->count());

        // Test tag names
        $tagNames = $prompt->tags->pluck('name')->toArray();
        $this->assertContains('Marketing', $tagNames);
        $this->assertContains('Strategy', $tagNames);
    }

    public function test_user_profile_integration()
    {
        // Create detailed user profile
        $profile = UserProfile::create([
            'user_id' => $this->seller->id,
            'bio' => 'Expert AI prompt engineer with 5+ years experience',
            'website' => 'https://promptexpert.com',
            'location' => 'New York, NY',
            'social_links' => [
                'twitter' => 'https://twitter.com/promptexpert',
                'linkedin' => 'https://linkedin.com/in/promptexpert',
            ],
        ]);

        // Test profile relationship
        $this->assertInstanceOf(UserProfile::class, $this->seller->profile);
        $this->assertEquals($profile->id, $this->seller->profile->id);
        $this->assertEquals($this->seller->id, $profile->user->id);

        // Test profile data
        $this->assertEquals('Expert AI prompt engineer with 5+ years experience', $profile->bio);
        $this->assertEquals('https://promptexpert.com', $profile->website);
        $this->assertEquals('New York, NY', $profile->location);
        $this->assertArrayHasKey('twitter', $profile->social_links);
        $this->assertArrayHasKey('linkedin', $profile->social_links);
    }

    public function test_review_moderation_workflow()
    {
        // Create prompt and purchase
        $prompt = Prompt::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Test Prompt for Review',
            'description' => 'A prompt for testing reviews',
            'content' => 'Test content...',
            'excerpt' => 'Test excerpt',
            'price' => 15.99,
            'price_type' => 'fixed',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $purchase = Purchase::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'price_paid' => 15.99,
            'payment_gateway' => 'stripe',
            'transaction_id' => 'txn_review_test',
            'purchased_at' => now(),
        ]);

        // Create unapproved review
        $review = Review::create([
            'user_id' => $this->buyer->id,
            'prompt_id' => $prompt->id,
            'rating' => 4,
            'title' => 'Good prompt',
            'review_text' => 'This prompt was helpful.',
            'is_approved' => false,
            'verified_purchase' => true,
        ]);

        // Test review is not approved initially
        $this->assertFalse($review->isApproved());
        $this->assertTrue($review->isVerifiedPurchase());

        // Admin approves review
        $review->update(['is_approved' => true]);
        $review->refresh();

        $this->assertTrue($review->isApproved());
    }
}
