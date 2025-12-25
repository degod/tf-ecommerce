<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductViewController extends Controller
{
    public function show(Product $product): Response
    {
        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'image' => 'https://placehold.co/300x300',
                'description' => $product->description,
            ],
        ]);
    }
}
