<?php

namespace Tests\Feature\Repositories\OrderItem;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\OrderItem\OrderItemRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderItemRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(OrderItemRepositoryInterface::class);
    }

    public function test_it_creates_an_order_item(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        $item = $this->repository->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 2,
            'subtotal' => $product->price * 2,
        ]);

        $this->assertInstanceOf(OrderItem::class, $item);
        $this->assertDatabaseHas('order_items', ['id' => $item->id]);
    }

    public function test_it_gets_items_by_order(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $items = $this->repository->getByOrder($order->id);

        $this->assertCount(3, $items);
    }
}
