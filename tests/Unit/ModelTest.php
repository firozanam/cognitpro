<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Prompt;
use App\Models\Purchase;
use App\Models\Review;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;
    protected Prompt $prompt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'email_verified_at' => now(),
        ]);

        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category description',
            'color' => '#3B82F6',
            'icon' => '📝',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->prompt = Prompt::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Test Prompt',
            'description' => 'Test prompt description',
            'content' => 'Test prompt content',
            'excerpt' => 'Test excerpt',
            'price' => 9.99,
            'price_type' => 'fixed',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_user_model_relationships()
    {
        // Test user has prompts relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->user->prompts);
        $this->assertEquals(1, $this->user->prompts->count());
        $this->assertEquals($this->prompt->id, $this->user->prompts->first()->id);

        // Test user role methods
        $this->assertTrue($this->user->isSeller());
        $this->assertFalse($this->user->isBuyer());
        $this->assertFalse($this->user->isAdmin());
        $this->assertTrue($this->user->canSell());
        $this->assertTrue($this->user->canBuy());

        // Test buyer user
        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
            'email_verified_at' => now(),
        ]);

        $this->assertTrue($buyer->isBuyer());
        $this->assertFalse($buyer->isSeller());
        $this->assertTrue($buyer->canBuy());
        $this->assertFalse($buyer->canSell());
    }

    public function test_user_profile_relationship()
    {
        $profile = UserProfile::create([
            'user_id' => $this->user->id,
            'bio' => 'Test bio',
            'website' => 'https://example.com',
            'location' => 'Test Location',
        ]);

        $this->assertInstanceOf(UserProfile::class, $this->user->profile);
        $this->assertEquals($profile->id, $this->user->profile->id);
        $this->assertEquals($this->user->id, $profile->user->id);
    }

    public function test_category_model_relationships()
    {
        // Test category has prompts relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->category->prompts);
        $this->assertEquals(1, $this->category->prompts->count());
        $this->assertEquals($this->prompt->id, $this->category->prompts->first()->id);

        // Test category methods
        $this->assertTrue($this->category->isActive());
        
        // Test inactive category
        $inactiveCategory = Category::create([
            'name' => 'Inactive Category',
            'slug' => 'inactive-category',
            'description' => 'Inactive category',
            'color' => '#FF0000',
            'icon' => '❌',
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $this->assertFalse($inactiveCategory->isActive());
    }

    public function test_prompt_model_relationships()
    {
        // Test prompt belongs to user
        $this->assertInstanceOf(User::class, $this->prompt->user);
        $this->assertEquals($this->user->id, $this->prompt->user->id);

        // Test prompt belongs to category
        $this->assertInstanceOf(Category::class, $this->prompt->category);
        $this->assertEquals($this->category->id, $this->prompt->category->id);

        // Test prompt has tags relationship
        $tag = Tag::create([
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'description' => 'Test tag description',
        ]);

        $this->prompt->tags()->attach($tag->id);
        $this->prompt->refresh();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->prompt->tags);
        $this->assertEquals(1, $this->prompt->tags->count());
        $this->assertEquals($tag->id, $this->prompt->tags->first()->id);
    }

    public function test_prompt_model_methods()
    {
        // Test published status
        $this->assertTrue($this->prompt->isPublished());
        
        // Test draft status
        $this->prompt->update(['status' => 'draft']);
        $this->assertFalse($this->prompt->isPublished());

        // Test effective price
        $this->assertEquals(9.99, $this->prompt->getEffectivePrice());

        // Test free prompt
        $freePrompt = Prompt::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Free Prompt',
            'description' => 'Free prompt description',
            'content' => 'Free prompt content',
            'excerpt' => 'Free excerpt',
            'price' => 0.00,
            'price_type' => 'free',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertEquals(0.00, $freePrompt->getEffectivePrice());

        // Test pay what you want prompt
        $pwywPrompt = Prompt::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'PWYW Prompt',
            'description' => 'Pay what you want prompt',
            'content' => 'PWYW prompt content',
            'excerpt' => 'PWYW excerpt',
            'price' => 5.00,
            'minimum_price' => 1.00,
            'price_type' => 'pay_what_you_want',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertEquals(5.00, $pwywPrompt->getEffectivePrice());
    }

    public function test_prompt_purchase_relationship()
    {
        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
            'email_verified_at' => now(),
        ]);

        // Test prompt not purchased initially
        $this->assertFalse($this->prompt->isPurchasedBy($buyer));

        // Create purchase
        $purchase = Purchase::create([
            'user_id' => $buyer->id,
            'prompt_id' => $this->prompt->id,
            'price_paid' => 9.99,
            'payment_gateway' => 'stripe',
            'transaction_id' => 'txn_test_123',
            'purchased_at' => now(),
        ]);

        // Test prompt is now purchased
        $this->assertTrue($this->prompt->isPurchasedBy($buyer));

        // Test purchase relationships
        $this->assertInstanceOf(User::class, $purchase->user);
        $this->assertInstanceOf(Prompt::class, $purchase->prompt);
        $this->assertEquals($buyer->id, $purchase->user->id);
        $this->assertEquals($this->prompt->id, $purchase->prompt->id);
    }

    public function test_review_model_relationships()
    {
        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
            'email_verified_at' => now(),
        ]);

        $review = Review::create([
            'user_id' => $buyer->id,
            'prompt_id' => $this->prompt->id,
            'rating' => 5,
            'title' => 'Great prompt!',
            'review_text' => 'This prompt is excellent.',
            'is_approved' => true,
            'verified_purchase' => true,
        ]);

        // Test review relationships
        $this->assertInstanceOf(User::class, $review->user);
        $this->assertInstanceOf(Prompt::class, $review->prompt);
        $this->assertEquals($buyer->id, $review->user->id);
        $this->assertEquals($this->prompt->id, $review->prompt->id);

        // Test review methods
        $this->assertTrue($review->isApproved());
        $this->assertTrue($review->isVerifiedPurchase());

        // Test prompt has reviews
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $this->prompt->reviews);
        $this->assertEquals(1, $this->prompt->reviews->count());
    }

    public function test_payment_model()
    {
        $payment = Payment::create([
            'user_id' => $this->user->id,
            'amount' => 9.99,
            'currency' => 'USD',
            'payment_gateway' => 'stripe',
            'transaction_id' => 'pi_test_123',
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        // Test payment relationships
        $this->assertInstanceOf(User::class, $payment->user);
        $this->assertEquals($this->user->id, $payment->user->id);

        // Test payment methods
        $this->assertTrue($payment->isCompleted());

        // Test pending payment
        $pendingPayment = Payment::create([
            'user_id' => $this->user->id,
            'amount' => 19.99,
            'currency' => 'USD',
            'payment_gateway' => 'stripe',
            'transaction_id' => 'pi_test_456',
            'status' => 'pending',
        ]);

        $this->assertFalse($pendingPayment->isCompleted());
    }

    public function test_tag_model_relationships()
    {
        $tag = Tag::create([
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'description' => 'Test tag description',
        ]);

        // Attach tag to prompt
        $this->prompt->tags()->attach($tag->id);

        // Test tag has prompts relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $tag->prompts);
        $this->assertEquals(1, $tag->prompts->count());
        $this->assertEquals($this->prompt->id, $tag->prompts->first()->id);
    }

    public function test_model_uuid_generation()
    {
        // Test that UUIDs are automatically generated
        $this->assertNotNull($this->prompt->uuid);
        $this->assertIsString($this->prompt->uuid);
        $this->assertEquals(36, strlen($this->prompt->uuid)); // UUID v4 length

        $purchase = Purchase::create([
            'user_id' => $this->user->id,
            'prompt_id' => $this->prompt->id,
            'price_paid' => 9.99,
            'payment_gateway' => 'stripe',
            'transaction_id' => 'txn_test_123',
            'purchased_at' => now(),
        ]);

        $this->assertNotNull($purchase->uuid);
        $this->assertIsString($purchase->uuid);
        $this->assertEquals(36, strlen($purchase->uuid));

        $payment = Payment::create([
            'user_id' => $this->user->id,
            'amount' => 9.99,
            'currency' => 'USD',
            'payment_gateway' => 'stripe',
            'transaction_id' => 'pi_test_123',
            'status' => 'completed',
        ]);

        $this->assertNotNull($payment->uuid);
        $this->assertIsString($payment->uuid);
        $this->assertEquals(36, strlen($payment->uuid));
    }
}
