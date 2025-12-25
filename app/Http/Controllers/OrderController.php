<?php

namespace App\Http\Controllers;

use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\OrderItem\OrderItemRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Jobs\NotifyLowStockJob;

class OrderController extends Controller
{
    private const STOCK_THRESHOLD = 20;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepo,
        private readonly OrderItemRepositoryInterface $orderItemRepo,
        private readonly CartRepositoryInterface $cartRepo
    ) {}

    /**
     * Display all orders of the authenticated user
     */
    public function index(): Response
    {
        $user = Auth::user();

        $orders = $this->orderRepo->getByUser($user->id)
            ->map(function ($order) {
                $order->items = $this->orderItemRepo->getByOrder($order->id);
                return $order;
            });

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Place a new order from the user's cart
     */
    public function store(): RedirectResponse
    {
        $user = Auth::user();

        $cartItems = $this->cartRepo->getUserCart($user->id);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        DB::beginTransaction();

        try {
            $lowStockProducts = [];

            foreach ($cartItems as $item) {
                $product = $item->product()->lockForUpdate()->first();

                if ($product->stock_quantity < $item->quantity) {
                    DB::rollBack();
                    return redirect()->route('cart.index')
                        ->with('error', "Not enough stock for {$product->name}.");
                }

                $product->decrement('stock_quantity', $item->quantity);

                // Collect products below threshold
                if ($product->stock_quantity < self::STOCK_THRESHOLD) {
                    $lowStockProducts[] = $product;
                }
            }

            $order = $this->orderRepo->create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total_amount' => $cartItems->sum(fn($item) => $item->quantity * $item->product->price),
            ]);

            $this->orderItemRepo->createFromCart($order, $cartItems);
            $this->cartRepo->clearUserCart($user->id);

            DB::commit();

            // Dispatch background job if any product is low
            if (!empty($lowStockProducts)) {
                NotifyLowStockJob::dispatch($lowStockProducts);
            }

            return redirect()->route('orders.index')
                ->with('success', 'Order placed successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('cart.index')
                ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Show a single order details
     */
    public function show(int $orderId): Response
    {
        $order = $this->orderRepo->findById($orderId);

        if (!$order || $order->user_id !== Auth::id()) {
            abort(404);
        }

        $order->items = $this->orderItemRepo->getByOrder($order->id);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }
}
