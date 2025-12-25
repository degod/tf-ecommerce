<?php

namespace App\Repositories\OrderItem;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

interface OrderItemRepositoryInterface
{
    public function create(array $data): OrderItem;

    public function createFromCart(Order $order, Collection $cartItems): void;

    public function getByOrder(int $orderId): Collection;
}
