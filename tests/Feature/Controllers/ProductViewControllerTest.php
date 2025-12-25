<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProductViewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_show_displays_product_details(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock_quantity' => 10,
        ]);

        $response = $this->actingAs($user)->get(route('products.show', $product));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Products/Show')
                 ->where('product.id', $product->id)
                 ->where('product.name', 'Test Product')
                 ->where('product.price', '99.99')
                 ->where('product.stock_quantity', 10)
        );
    }
}
