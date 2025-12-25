import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';

export default function Show({ product }) {
    const [quantity, setQuantity] = useState(1);

    const subtotal = useMemo(() => {
        return (quantity * product.price).toFixed(2);
    }, [quantity, product.price]);

    const increase = () => {
        if (quantity < product.stock_quantity) {
            setQuantity(quantity + 1);
        }
    };

    const decrease = () => {
        if (quantity > 1) {
            setQuantity(quantity - 1);
        }
    };

    const confirmAddToCart = () => {
        router.post(route('cart.store'), {
            product_id: product.id,
            quantity,
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={product.name} />

            <div className="max-w-7xl mx-auto py-12 px-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-10 bg-white p-8 rounded shadow">

                    <div className="flex justify-center">
                        <img src={product.image}
                            alt={product.name}
                            className="rounded-lg w-full max-w-sm"/>
                    </div>

                    <div className="space-y-6">
                        <h1 className="text-2xl font-bold truncate">
                            {product.name}
                        </h1>

                        <p className="text-gray-600">
                            {product.description}
                        </p>

                        <div className="space-y-2">
                            <p className="text-lg font-semibold">
                                Price per unit:
                                <span className="ml-2 text-green-600">
                                    €{Number(product.price).toLocaleString()}
                                </span>
                            </p>

                            <p className="text-sm text-gray-500">
                                Stock left: {product.stock_quantity}
                            </p>
                        </div>

                        <div className="flex items-center gap-4">
                            <button onClick={decrease}
                                className="px-4 py-2 border rounded text-lg">
                                −
                            </button>

                            <span className="text-lg font-semibold w-8 text-center">
                                {quantity}
                            </span>

                            <button onClick={increase}
                                className="px-4 py-2 border rounded text-lg">
                                +
                            </button>
                        </div>

                        <div className="text-xl font-semibold">
                            Subtotal:
                            <span className="ml-2 text-blue-600">
                                €{Number(subtotal).toLocaleString()}
                            </span>
                        </div>

                        <button onClick={confirmAddToCart}
                            className="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded font-semibold">
                            Confirm Addition to Cart
                        </button>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
