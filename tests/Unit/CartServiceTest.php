<?php

namespace Tests\Unit;

use App\Modules\Prompt\Contracts\CartRepositoryInterface;
use App\Modules\Prompt\Contracts\PurchaseRepositoryInterface;
use App\Modules\Prompt\Models\Cart;
use App\Modules\Prompt\Models\Prompt;
use App\Modules\Prompt\Services\CartService;
use App\Modules\Prompt\Services\PurchaseService;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    protected CartService $cartService;

    protected $cartRepository;

    protected $purchaseRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartRepository = Mockery::mock(CartRepositoryInterface::class);
        $this->purchaseRepository = Mockery::mock(PurchaseRepositoryInterface::class);

        $this->cartService = new CartService(
            $this->cartRepository,
            $this->purchaseRepository,
            $this->app->make(PurchaseService::class)
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_gets_cart_items_for_user(): void
    {
        $userId = 1;

        $items = new Collection([
            Mockery::mock(Cart::class),
            Mockery::mock(Cart::class),
        ]);

        $this->cartRepository
            ->shouldReceive('getWithPrompts')
            ->with($userId)
            ->once()
            ->andReturn($items);

        $result = $this->cartService->getCartItems($userId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_gets_cart_count(): void
    {
        $userId = 1;

        $this->cartRepository
            ->shouldReceive('getCountByUserId')
            ->with($userId)
            ->once()
            ->andReturn(5);

        $count = $this->cartService->getCartCount($userId);

        $this->assertEquals(5, $count);
    }

    #[Test]
    public function it_adds_prompt_to_cart(): void
    {
        $user = new User;
        $user->id = 1;

        $prompt = new Prompt;
        $prompt->id = 1;
        $prompt->seller_id = 2;
        $prompt->price = 9.99;
        $prompt->status = 'approved';

        $cart = new Cart;

        $this->purchaseRepository
            ->shouldReceive('hasPurchased')
            ->with($user->id, $prompt->id)
            ->once()
            ->andReturn(false);

        $this->cartRepository
            ->shouldReceive('hasPrompt')
            ->with($user->id, $prompt->id)
            ->once()
            ->andReturn(false);

        $this->cartRepository
            ->shouldReceive('create')
            ->with([
                'user_id' => $user->id,
                'prompt_id' => $prompt->id,
                'price' => $prompt->price,
            ])
            ->once()
            ->andReturn($cart);

        $result = $this->cartService->addToCart($user, $prompt);

        $this->assertInstanceOf(Cart::class, $result);
    }

    #[Test]
    public function it_prevents_adding_own_prompt(): void
    {
        $user = new User;
        $user->id = 1;

        $prompt = new Prompt;
        $prompt->id = 1;
        $prompt->seller_id = 1; // Same as user
        $prompt->status = 'approved';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot add your own prompt to the cart.');

        $this->cartService->addToCart($user, $prompt);
    }

    #[Test]
    public function it_prevents_adding_unapproved_prompt(): void
    {
        $user = new User;
        $user->id = 1;

        $prompt = new Prompt;
        $prompt->id = 1;
        $prompt->seller_id = 2;
        $prompt->status = 'draft';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This prompt is not available for purchase.');

        $this->cartService->addToCart($user, $prompt);
    }

    #[Test]
    public function it_prevents_adding_already_purchased_prompt(): void
    {
        $user = new User;
        $user->id = 1;

        $prompt = new Prompt;
        $prompt->id = 1;
        $prompt->seller_id = 2;
        $prompt->status = 'approved';

        $this->purchaseRepository
            ->shouldReceive('hasPurchased')
            ->with($user->id, $prompt->id)
            ->once()
            ->andReturn(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You already own this prompt.');

        $this->cartService->addToCart($user, $prompt);
    }

    #[Test]
    public function it_prevents_adding_duplicate_items(): void
    {
        $user = new User;
        $user->id = 1;

        $prompt = new Prompt;
        $prompt->id = 1;
        $prompt->seller_id = 2;
        $prompt->status = 'approved';

        $this->purchaseRepository
            ->shouldReceive('hasPurchased')
            ->with($user->id, $prompt->id)
            ->once()
            ->andReturn(false);

        $this->cartRepository
            ->shouldReceive('hasPrompt')
            ->with($user->id, $prompt->id)
            ->once()
            ->andReturn(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This prompt is already in your cart.');

        $this->cartService->addToCart($user, $prompt);
    }

    #[Test]
    public function it_removes_item_from_cart(): void
    {
        $userId = 1;
        $promptId = 1;

        $cartItem = Mockery::mock(Cart::class);

        $this->cartRepository
            ->shouldReceive('findByUserAndPrompt')
            ->with($userId, $promptId)
            ->once()
            ->andReturn($cartItem);

        $this->cartRepository
            ->shouldReceive('delete')
            ->with($cartItem)
            ->once()
            ->andReturn(true);

        $result = $this->cartService->removeFromCart($userId, $promptId);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_throws_exception_when_removing_nonexistent_item(): void
    {
        $userId = 1;
        $promptId = 999;

        $this->cartRepository
            ->shouldReceive('findByUserAndPrompt')
            ->with($userId, $promptId)
            ->once()
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Item not found in cart.');

        $this->cartService->removeFromCart($userId, $promptId);
    }

    #[Test]
    public function it_clears_cart(): void
    {
        $userId = 1;

        $this->cartRepository
            ->shouldReceive('deleteByUserId')
            ->with($userId)
            ->once()
            ->andReturn(true);

        $result = $this->cartService->clearCart($userId);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_checks_if_prompt_is_in_cart(): void
    {
        $userId = 1;
        $promptId = 1;

        $this->cartRepository
            ->shouldReceive('hasPrompt')
            ->with($userId, $promptId)
            ->once()
            ->andReturn(true);

        $result = $this->cartService->isInCart($userId, $promptId);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_gets_cart_summary(): void
    {
        $userId = 1;

        $prompt1 = new Prompt;
        $prompt1->price = 9.99;
        $prompt1->status = 'approved';

        $prompt2 = new Prompt;
        $prompt2->price = 19.99;
        $prompt2->status = 'approved';

        $item1 = new Cart;
        $item1->setRelation('prompt', $prompt1);

        $item2 = new Cart;
        $item2->setRelation('prompt', $prompt2);

        $items = new Collection([$item1, $item2]);

        $this->cartRepository
            ->shouldReceive('getWithPrompts')
            ->with($userId)
            ->once()
            ->andReturn($items);

        $summary = $this->cartService->getCartSummary($userId);

        $this->assertArrayHasKey('items', $summary);
        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('count', $summary);
        $this->assertEquals(29.98, $summary['total']);
        $this->assertEquals(2, $summary['count']);
    }
}
