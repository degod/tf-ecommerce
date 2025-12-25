<?php

namespace Tests\Feature\Repositories\Order;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepositoryInterface $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepository = $this->app->make(OrderRepositoryInterface::class);
    }

    public function test_it_creates_an_order(): void
    {
        $user = User::factory()->create();

        $order = $this->orderRepository->create([
            'user_id' => $user->id,
            'total_amount' => 15000,
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_it_gets_orders_by_user(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id]);

        $orders = $this->orderRepository->getByUser($user->id);

        $this->assertCount(2, $orders);
    }

    public function test_it_updates_order_status(): void
    {
        $order = Order::factory()->create();

        $result = $this->orderRepository->updateStatus($order, 'paid');

        $this->assertTrue($result);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }
}
