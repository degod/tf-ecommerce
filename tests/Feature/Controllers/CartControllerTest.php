<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Cart\CartRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_index_displays_user_cart_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertInertia(fn($page) => $page
            ->component('Cart/Index')
            ->has('cartItems')
        );
    }

    public function test_guest_cannot_access_cart(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_store_adds_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 3,
            '_token' => 'test-token',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success', 'Product added to cart');

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_store_validates_product_id_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('cart.store'), [
            'product_id' => 99999,
            'quantity' => 1,
            '_token' => 'test-token',
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_store_validates_quantity_is_required(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('cart.store'), [
            'product_id' => $product->id,
            '_token' => 'test-token',
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_store_validates_quantity_is_minimum_one(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 0,
            '_token' => 'test-token',
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_update_changes_cart_item_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->patch(route('cart.update', $cart), [
            'quantity' => 5,
            '_token' => 'test-token',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Cart updated');

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'quantity' => 5,
        ]);
    }

    public function test_update_validates_quantity_is_required(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->patch(route('cart.update', $cart), [
            '_token' => 'test-token',
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_user_cannot_update_another_users_cart_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        
        $cart = Cart::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user2)
        ->withSession(['_token' => 'test-token'])
        ->patch(route('cart.update', $cart), [
            'quantity' => 5,
            '_token' => 'test-token',
        ]);

        $response->assertForbidden();
    }

    public function test_destroy_removes_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->delete(route('cart.destroy', $cart), ['_token' => 'test-token']);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Item removed from cart');

        $this->assertDatabaseMissing('carts', [
            'id' => $cart->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_cart_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        
        $cart = Cart::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user2)
        ->withSession(['_token' => 'test-token'])
        ->delete(route('cart.destroy', $cart), ['_token' => 'test-token']);

        $response->assertForbidden();
        
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
        ]);
    }

    public function test_clear_removes_all_user_cart_items(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
        ]);

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->delete(route('cart.clear'), ['_token' => 'test-token']);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Cart cleared');

        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
        ]);
    }

    public function test_clear_does_not_affect_other_users_carts(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        
        Cart::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);
        
        $user2Cart = Cart::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user1)->withSession(['_token' => 'test-token'])
        ->delete(route('cart.clear'), ['_token' => 'test-token']);

        $this->assertDatabaseHas('carts', [
            'id' => $user2Cart->id,
            'user_id' => $user2->id,
        ]);
    }

    public function test_guest_cannot_add_to_cart(): void
    {
        $product = Product::factory()->create();

        $response = $this->withSession(['_token' => 'test-token'])
        ->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            '_token' => 'test-token',
        ]);

        $response->assertRedirect(route('login'));
    }
}