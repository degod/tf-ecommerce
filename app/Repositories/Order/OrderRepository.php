<?php

namespace App\Repositories\Order;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly Order $model
    ) {}

    public function create(array $data): Order
    {
        return $this->model->newQuery()->create($data);
    }

    public function findById(int $id): ?Order
    {
        return $this->model->newQuery()->find($id);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function updateStatus(Order $order, string $status): bool
    {
        return $order->update(['status' => $status]);
    }
}
