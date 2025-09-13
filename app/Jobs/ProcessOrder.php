<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\ApiException;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $userId)
    {
        $this->data = $data;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $totalAmount = 0;

            // Valida o estoque e calcula o total
            foreach ($this->data['products'] as $productData) {
                $product = Product::findOrFail($productData['product_id']);

                if ($product->stock < $productData['quantity']) {
                    throw new \Exception('Produto ' . $product->name . ' não tem estoque suficiente.');
                }

                $totalAmount += $product->price * $productData['quantity'];
            }

            // Cria o pedido
            $order = Order::create([
                'user_id' => $this->userId,
                'total_amount' => $totalAmount,
                'status' => 'pendente',
            ]);

            // Vincula os produtos ao pedido e atualiza o estoque
            foreach ($this->data['products'] as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                
                $order->products()->attach($productData['product_id'], [
                    'quantity' => $productData['quantity'],
                    'price' => $product->price
                ]);

                $product->decrement('stock', $productData['quantity']);
            }

            // Após o sucesso, invalida o cache
            Cache::forget('orders');
        });
    }
}
