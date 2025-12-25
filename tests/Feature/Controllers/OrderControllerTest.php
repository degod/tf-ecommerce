<?php

namespace Tests\Feature\Controllers;

use App\Jobs\NotifyLowStockJob;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_index_displays_users_orders(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Orders/Index')
                 ->has('orders', 1)
                 ->where('orders.0.id', $order->id)
        );
    }

    public function test_store_places_order_decrements_stock_and_clears_cart(): void
    {
        Bus::fake();

        $user = User::factory()->create();

        $product = Product::factory()->create([
            'price' => 100,
            'stock_quantity' => 50,
        ]);

        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('orders.store'), ['_token' => 'test-token']);

        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 500,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 5,
            'subtotal' => 500,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 45,
        ]);
        $this->assertDatabaseMissing('carts', [
            'user_id' => $user->id,
        ]);

        Bus::assertNotDispatched(NotifyLowStockJob::class);
    }

    public function test_store_dispatches_low_stock_notification_when_threshold_is_crossed(): void
    {
        Bus::fake();

        $user = User::factory()->create();

        $product = Product::factory()->create([
            'price' => 50,
            'stock_quantity' => 22, // close to threshold
        ]);

        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('orders.store'), ['_token' => 'test-token']);

        Bus::assertDispatched(NotifyLowStockJob::class);
    }

    public function test_store_fails_if_insufficient_stock(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'stock_quantity' => 2,
        ]);

        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response = $this->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post(route('orders.store'), ['_token' => 'test-token']);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_show_displays_order_details_for_owner(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(
            route('orders.show', $order->id)
        );

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Orders/Show')
                 ->where('order.id', $order->id)
        );
    }

    public function test_show_returns_404_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($intruder)
             ->get(route('orders.show', $order->id))
             ->assertNotFound();
    }
}
