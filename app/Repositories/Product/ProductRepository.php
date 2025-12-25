<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly Product $model
    ) {}

    public function all(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function findById(int $id): ?Product
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): Product
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function decrementStock(Product $product, int $quantity): bool
    {
        if ($product->stock_quantity < $quantity) {
            return false;
        }

        return $product->update([
            'stock_quantity' => $product->stock_quantity - $quantity,
        ]);
    }
}
