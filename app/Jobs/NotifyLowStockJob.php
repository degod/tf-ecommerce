<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $productData;

    public function __construct(array $productData)
    {
        $this->productData = $productData;
    }

    public function handle(): void
    {
        $email = config('mail.threshold_admin_email', env('THRESHOLD_ADMIN_EMAIL'));

        if (!$email) {
            return;
        }

        $html = $this->buildEmailHtml($this->productData);

        Mail::html($html, function ($message) use ($email) {
            $message->to($email)
                ->subject('Low Stock Alert - ' . now()->format('F j, Y'));
        });
    }

    private function buildEmailHtml(array $products): string
    {
        $date = now()->format('F j, Y g:i A');
        $productsCount = count($products);
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 20px; 
                    background-color: #f5f5f5;
                    margin: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: white;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .header { 
                    background-color: #f59e0b; 
                    color: white; 
                    padding: 30px 20px;
                    text-align: center;
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 24px;
                }
                .header p {
                    margin: 10px 0 0 0;
                    opacity: 0.9;
                    font-size: 14px;
                }
                .content {
                    padding: 30px 20px;
                }
                .alert-box {
                    background-color: #fef3c7;
                    border-left: 4px solid #f59e0b;
                    padding: 15px;
                    margin-bottom: 25px;
                    border-radius: 4px;
                }
                .alert-box p {
                    margin: 0;
                    color: #78350f;
                    font-weight: 500;
                }
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                    margin-top: 10px;
                }
                th { 
                    background-color: #f3f4f6; 
                    color: #374151; 
                    padding: 12px; 
                    text-align: left;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                td { 
                    padding: 12px; 
                    border-bottom: 1px solid #e5e7eb;
                    color: #1f2937;
                }
                tr:last-child td {
                    border-bottom: none;
                }
                .product-name {
                    font-weight: 600;
                }
                .stock-quantity {
                    color: #dc2626;
                    font-weight: 600;
                    text-align: center;
                }
                .footer { 
                    padding: 20px; 
                    background-color: #f9fafb;
                    text-align: center;
                    color: #6b7280; 
                    font-size: 12px;
                    border-top: 1px solid #e5e7eb;
                }
                .footer p {
                    margin: 5px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⚠️ Low Stock Alert ⚠️</h1>
                    <p>{$date}</p>
                </div>
                
                <div class='content'>
                    <div class='alert-box'>
                        <p>{$productsCount} " . ($productsCount === 1 ? 'product has' : 'products have') . " fallen below the stock threshold and require immediate attention.</p>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th style='text-align: center; width: 150px;'>Current Stock</th>
                            </tr>
                        </thead>
                        <tbody>";

        foreach ($products as $product) {
            $html .= "
                            <tr>
                                <td class='product-name'>{$product['name']}</td>
                                <td class='stock-quantity'>{$product['stock_quantity']}</td>
                            </tr>";
        }

        $html .= "
                        </tbody>
                    </table>
                </div>
                
                <div class='footer'>
                    <p><strong>Action Required:</strong> Please restock these products as soon as possible.</p>
                    <p>This is an automated alert generated on " . now()->format('Y-m-d H:i:s') . "</p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }
}