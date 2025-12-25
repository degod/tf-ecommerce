<?php

namespace App\Repositories\OrderItem;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    public function __construct(
        private readonly OrderItem $model
    ) {}

    public function create(array $data): OrderItem
    {
        return $this->model->newQuery()->create($data);
    }

    public function createFromCart(Order $order, Collection $cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $this->model->newQuery()->create([
                'order_id'      => $order->id,
                'product_id'    => $cartItem->product->id,
                'product_name'  => $cartItem->product->name,
                'product_price' => $cartItem->product->price,
                'quantity'      => $cartItem->quantity,
                'subtotal'      => $cartItem->product->price * $cartItem->quantity,
            ]);
        }
    }

    public function getByOrder(int $orderId): Collection
    {
        return $this->model->newQuery()
            ->where('order_id', $orderId)
            ->get();
    }
}
