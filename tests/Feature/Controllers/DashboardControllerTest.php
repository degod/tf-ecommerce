<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_index_displays_products(): void
    {
        $user = User::factory()->create();

        Product::factory()->create(['name' => 'Product A']);
        Product::factory()->create(['name' => 'Product B']);

        $response = $this->actingAs($user)->get(route('products'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Dashboard')
                 ->has('products', 2)
                 ->where('products.0.name', 'Product B')
                 ->where('products.1.name', 'Product A')
        );
    }
}
