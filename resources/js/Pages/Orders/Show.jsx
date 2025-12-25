import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function OrderShow({ order }) {
    const orderItems = order.items;
    const orderTotal = orderItems.reduce(
        (sum, item) => sum + item.subtotal,
        0
    );

    return (
        <AuthenticatedLayout
            header={
                <h2 className="flex items-center justify-between text-xl font-semibold leading-tight text-gray-800">
				    <span>Order Details</span>
				</h2>
            }>
            <Head title="Order Details" />

            <div className="py-3">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-3">

                    {orderItems.map((item) => (
                        <div key={item.id}
                            className="flex gap-3 rounded bg-white p-4 shadow">
                            <img src="https://placehold.co/60x60"
                                alt={item.product_name}
                                className="h-12 w-12 rounded object-cover"/>

                            <div className="flex-1">
                                <h3 className="font-semibold text-lg truncate">
                                    {item.product_name}
                                </h3>

                                <p className="text-sm text-gray-600">
                                    €{item.product_price.toLocaleString()} per unit
                                </p>
                            </div>

                            <div className="flex flex-col items-end justify-between">
                                <p className="font-semibold">
                                    €{(item.quantity * item.product_price).toLocaleString()}
                                </p>
                            </div>
                        </div>
                    ))}

                    {orderItems.length > 0 && (
                        <div className="flex justify-end rounded bg-white p-4 shadow">
                            <div className="text-right space-y-3">
                                <p className="text-lg font-semibold">
                                    Total: €{Number(order.total_amount).toLocaleString()}
                                </p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
