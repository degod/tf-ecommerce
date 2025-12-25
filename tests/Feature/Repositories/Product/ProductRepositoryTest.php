<?php

namespace Tests\Feature\Repositories\Product;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\Product\ProductRepositoryInterface;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepositoryInterface $products;

    protected function setUp(): void
    {
        parent::setUp();
        $this->products = $this->app->make(ProductRepositoryInterface::class);
    }

    public function test_it_can_create_a_product(): void
    {
        $product = $this->products->create([
            'name' => 'Test Product',
            'price' => 199.99,
            'stock_quantity' => 10,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    public function test_it_can_find_a_product_by_id(): void
    {
        $product = Product::factory()->create();

        $found = $this->products->findById($product->id);

        $this->assertNotNull($found);
        $this->assertSame($product->id, $found->id);
    }

    public function test_it_can_decrement_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $result = $this->products->decrementStock($product, 2);

        $this->assertTrue($result);
        $this->assertSame(3, $product->fresh()->stock_quantity);
    }

    public function test_it_prevents_stock_underflow(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 1]);

        $result = $this->products->decrementStock($product, 5);

        $this->assertFalse($result);
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }
}
