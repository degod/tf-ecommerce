import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ products }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Products
                </h2>
            }>
            <Head title="Products" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        {products.length === 0 && (
                            <p className="text-gray-500">
                                No products available.
                            </p>
                        )}

                        {products.map((product) => (
                            <div key={product.id}
                                className="overflow-hidden bg-white rounded-lg shadow-sm border">
                                <img src="https://placehold.co/300x300"
                                    alt={product.name}
                                    className="w-full h-48 object-cover"/>

                                <div className="p-4">
                                    <h3 className="text-lg font-semibold text-gray-900 truncate">
                                        {product.name}
                                    </h3>

                                    <p className="mt-2 text-gray-700 font-medium">
                                        €{Number(product.price).toLocaleString()}
                                    </p>

                                    <p className="mt-1 text-sm text-gray-500">
                                        Stock: {product.stock_quantity} unit(s)
                                    </p>

                                    <Link href={route('products.show', product.id)}
                                        className="mt-4 w-full rounded-md bg-indigo-600 px-4 py-2 text-white text-sm font-medium hover:bg-indigo-700 transition" as="button" type="button"
                                        disabled={product.stock_quantity === 0}>
                                        {product.stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart'}
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
