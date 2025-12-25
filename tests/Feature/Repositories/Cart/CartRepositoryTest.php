<?php

namespace Tests\Feature\Repositories\Cart;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\Cart\CartRepositoryInterface;

class CartRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CartRepositoryInterface $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cart = $this->app->make(CartRepositoryInterface::class);
    }

    public function test_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = $this->cart->addProduct(
            $user->id,
            $product->id,
            2
        );

        $this->assertInstanceOf(Cart::class, $cartItem);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_cart_returns_all_products(): void
    {
        $user = User::factory()->create();

        Cart::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $items = $this->cart->getUserCart($user->id);

        $this->assertCount(3, $items);
    }

    public function test_user_can_clear_cart(): void
    {
        $user = User::factory()->create();

        Cart::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $this->cart->clearUserCart($user->id);

        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
        ]);
    }
}
