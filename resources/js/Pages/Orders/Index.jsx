import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import dayjs from 'dayjs';
import advancedFormat from 'dayjs/plugin/advancedFormat';

dayjs.extend(advancedFormat);

export default function OrdersIndex({ orders }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    My Orders
                </h2>
            }>
            <Head title="My Orders" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded bg-white shadow">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Order #
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Item Count
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total (€)
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {orders.map((order) => (
                                    <tr key={order.id}>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            #{order.id}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {order.items.length} item{order.items.length > 1 ? 's' : ''}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            €{Number(order.total_amount).toLocaleString()}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {dayjs(order.created_at).format('MMM Do YYYY, h:mm A')}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <Link href={`/orders/${order.id}`}
                                                className="rounded bg-indigo-600 px-3 py-1 text-white text-sm hover:bg-indigo-700">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {orders.length === 0 && (
                                    <tr>
                                        <td colSpan={5}
                                            className="px-6 py-4 text-center text-gray-500">
                                            You have no orders yet.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
