<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

class CartRepository implements CartRepositoryInterface
{
    public function __construct(
        private readonly Cart $model
    ) {}

    public function getUserCart(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->with('product')
            ->where('user_id', $userId)
            ->get();
    }

    public function addProduct(
        int $userId,
        int $productId,
        int $quantity
    ): Cart {
        return $this->model->newQuery()->updateOrCreate(
            [
                'user_id' => $userId,
                'product_id' => $productId,
            ],
            [
                'quantity' => $quantity,
            ]
        );
    }

    public function updateQuantity(
        Cart $cart,
        int $quantity
    ): bool {
        return $cart->update([
            'quantity' => $quantity,
        ]);
    }

    public function removeProduct(Cart $cart): bool
    {
        return (bool) $cart->delete();
    }

    public function clearUserCart(int $userId): void
    {
        $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->delete();
    }
}
