<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Repositories\Cart\CartRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    public function index(): Response
    {
        $cartItems = $this->cartRepository->getUserCart(auth()->id());

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartItems,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $this->cartRepository->addProduct(
            auth()->id(),
            $validated['product_id'],
            $validated['quantity']
        );

        return redirect()
            ->route('cart.index')
            ->with('success', 'Product added to cart');
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        $this->authorize('update', $cart);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartRepository->updateQuantity(
            $cart,
            $validated['quantity']
        );

        return back()->with('success', 'Cart updated');
    }

    public function destroy(Cart $cart): RedirectResponse
    {
        $this->authorize('delete', $cart);

        $this->cartRepository->removeProduct($cart);

        return back()->with('success', 'Item removed from cart');
    }

    public function clear(): RedirectResponse
    {
        $this->cartRepository->clearUserCart(auth()->id());

        return back()->with('success', 'Cart cleared');
    }
}
