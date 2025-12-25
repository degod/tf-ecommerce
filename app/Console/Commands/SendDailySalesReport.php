<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDailySalesReport extends Command
{
    protected $signature = 'report:daily-sales';
    protected $description = 'Send daily sales report by products to admin';

    public function handle(): int
    {
        $email = config('mail.report_admin_email', env('REPORT_ADMIN_EMAIL'));

        $this->info('Fetching daily sales data...');
        try {
            $sales = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'products.id',
                    'products.name as product_name',
                    DB::raw('SUM(order_items.quantity) as total_quantity_sold'),
                    DB::raw('SUM(order_items.subtotal) as total_sales')
                )
                ->whereBetween('orders.created_at', [now()->startOfDay(), now()->endOfDay()])
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sales')
                ->get();

            if ($sales->isEmpty()) {
                $this->info('No sales in the last 24 hours.');
                return Command::SUCCESS;
            }

            $this->info("Found {$sales->count()} products with sales.");

            $totalRevenue = $sales->sum('total_sales');
            $totalQuantity = $sales->sum('total_quantity_sold');

            $html = $this->buildEmailHtml($sales, $totalRevenue, $totalQuantity);

            Mail::html($html, function ($message) use ($email) {
                $message->to($email)
                    ->subject('Daily Sales Report - ' . now()->format('F j, Y'));
            });

            $this->info('Daily sales report sent successfully to ' . $email);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to send daily sales report: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function buildEmailHtml($sales, $totalRevenue, $totalQuantity): string
    {
        $date = now()->format('F j, Y');
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h1 { color: #333; }
                table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                th { background-color: #4CAF50; color: white; padding: 12px; text-align: left; }
                td { padding: 10px; border-bottom: 1px solid #ddd; }
                tr:hover { background-color: #f5f5f5; }
                .summary { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
                .summary p { margin: 5px 0; font-size: 16px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <h1>Daily Sales Report</h1>
            <p><strong>Date:</strong> {$date}</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style='text-align: center;'>Quantity Sold</th>
                        <th style='text-align: right;'>Total Sales</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($sales as $row) {
            $formattedSales = '€' . number_format($row->total_sales, 2, '.', ',');
            $html .= "
                    <tr>
                        <td>{$row->product_name}</td>
                        <td style='text-align: center;'>{$row->total_quantity_sold}</td>
                        <td style='text-align: right;'>{$formattedSales}</td>
                    </tr>";
        }

        $formattedTotal = '€' . number_format($totalRevenue, 2, '.', ',');

        $html .= "
                </tbody>
            </table>
            
            <div class='summary'>
                <h3>Summary</h3>
                <p><strong>Total Products Sold:</strong> {$sales->count()}</p>
                <p><strong>Total Quantity:</strong> {$totalQuantity}</p>
                <p><strong>Total Revenue:</strong> {$formattedTotal}</p>
            </div>
            
            <div class='footer'>
                <p>This is an automated report generated on " . now()->format('Y-m-d H:i:s') . "</p>
            </div>
        </body>
        </html>";

        return $html;
    }
}