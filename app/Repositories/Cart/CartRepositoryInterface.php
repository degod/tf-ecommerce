<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

interface CartRepositoryInterface
{
    public function getUserCart(int $userId): Collection;

    public function addProduct(
        int $userId,
        int $productId,
        int $quantity
    ): Cart;

    public function updateQuantity(
        Cart $cart,
        int $quantity
    ): bool;

    public function removeProduct(Cart $cart): bool;

    public function clearUserCart(int $userId): void;
}
