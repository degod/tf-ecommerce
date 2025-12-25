import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function CartIndex({ cartItems }) {
    const [alert, setAlert] = useState('');

    const updateQuantity = (cartId, quantity, type) => {
        if (quantity < 1) return;

        router.patch(
            route('cart.update', cartId),
            { quantity },
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (type === 'increase') {
                        setAlert('Quantity increased!');
                        setTimeout(() => setAlert(''), 3000);
                    }
                    if (type === 'decrease') {
                        setAlert('Quantity decreased!');
                        setTimeout(() => setAlert(''), 3000);
                    }
                },
            }
        );
    };

    const removeItem = (cartId) => {
        router.delete(route('cart.destroy', cartId), {
            preserveScroll: true,
        });
    };

    const cartTotal = cartItems.reduce(
        (sum, item) => sum + item.quantity * item.product.price,
        0
    );

    const clearAllItems = () => {
	    if (!confirm('Are you sure you want to clear your cart?')) return;

	    router.delete(route('cart.clear'), {
	        preserveScroll: true,
	    });
	};

    return (
        <AuthenticatedLayout
            header={
                <h2 className="flex items-center justify-between text-xl font-semibold leading-tight text-gray-800">
				    <span>My Cart</span>

				    <button
				        onClick={clearAllItems}
				        className="rounded bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700"
				    >
				        Clear Cart
				    </button>
				</h2>
            }>
            <Head title="Cart" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                    {alert && (
					    <div className="mb-4 rounded px-4 py-2 text-white font-medium shadow" style={{backgroundColor: '#22c55e'}}>
					        {alert}
					    </div>
					)}

                    {cartItems.length === 0 && (
                        <div className="rounded bg-white p-6 text-center shadow">
                            Your cart is empty
                        </div>
                    )}

                    {cartItems.map((item) => (
                        <div key={item.id}
                            className="flex gap-6 rounded bg-white p-6 shadow">
                            <img src="https://placehold.co/120x120"
                                alt={item.product.name}
                                className="h-24 w-24 rounded object-cover"/>

                            <div className="flex-1">
                                <h3 className="font-semibold text-lg truncate">
                                    {item.product.name}
                                </h3>

                                <p className="text-sm text-gray-600">
                                    €{item.product.price.toLocaleString()} per unit
                                </p>

                                <div className="mt-4 flex items-center gap-3">
                                    <button onClick={() =>
                                            updateQuantity(item.id, item.quantity - 1, 'decrease')
                                        }
                                        className="rounded border px-3 py-1 hover:bg-gray-100">
                                        −
                                    </button>

                                    <span className="w-8 text-center">{item.quantity}</span>

                                    <button onClick={() =>
                                            updateQuantity(item.id, item.quantity + 1, 'increase')
                                        }
                                        className="rounded border px-3 py-1 hover:bg-gray-100">
                                        +
                                    </button>
                                </div>
                            </div>

                            <div className="flex flex-col items-end justify-between">
                                <p className="font-semibold">
                                    €{(item.quantity * item.product.price).toLocaleString()}
                                </p>

                                <button onClick={() => removeItem(item.id)}
                                    className="text-sm text-red-600 hover:underline">
                                    Remove
                                </button>
                            </div>
                        </div>
                    ))}

                    {cartItems.length > 0 && (
                        <div className="flex justify-end rounded bg-white p-6 shadow">
                            <div className="text-right space-y-3">
                                <p className="text-lg font-semibold">
                                    Total: €{cartTotal.toLocaleString()}
                                </p>

                                <button className="rounded bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700">
                                    Place Order
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
