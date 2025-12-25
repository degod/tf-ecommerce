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

    public array $products;

    public function __construct(array $products)
    {
        $this->products = $products;
    }

    public function handle(): void
    {
        $email = config('mail.admin_email', env('THRESHOLD_ADMIN_EMAIL'));

        if (!$email) {
            return;
        }

        $productList = collect($this->products)
            ->map(fn($p) => "{$p->name} (Stock: {$p->stock_quantity})")
            ->implode("\n");

        Mail::raw(
            "The following products are below the stock threshold:\n\n{$productList}",
            function ($message) use ($email) {
                $message->to($email)
                        ->subject('Low Stock Alert');
            }
        );
    }
}
