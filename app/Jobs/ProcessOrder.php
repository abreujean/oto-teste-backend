<?php

namespace App\Jobs;

use App\Exceptions\ApiException;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        DB::beginTransaction();

        // Atualiza o status para "em processamento"
        $this->order->update(['status' => 'em processamento']);
        $this->order->user->notify(new OrderStatusUpdated($this->order, 'em processamento', ''));

        try {
            $totalAmount = 0;

            // Valida o estoque e calcula o total
            foreach ($this->order->products as $product) {
                $productModel = Product::findOrFail($product->id);

                if ($productModel->stock < $product->pivot->quantity) {
                    throw new \Exception('Produto ' . $productModel->name . ' não tem estoque suficiente.');
                }

                $totalAmount += $productModel->price * $product->pivot->quantity;
            }

            // Atualiza o valor total do pedido
            $this->order->total_amount = $totalAmount;

            // Atualiza o estoque
            foreach ($this->order->products as $product) {
                $productModel = Product::findOrFail($product->id);
                $productModel->decrement('stock', $product->pivot->quantity);
            }
            
            DB::commit();

            // Atualiza o status para "concluído"
            $this->order->update(['status' => 'concluido']);
            $this->order->user->notify(new OrderStatusUpdated($this->order, 'concluido', ''));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->order->update(['status' => 'cancelado']);
            $this->order->user->notify(new OrderStatusUpdated($this->order, 'cancelado', $e->getMessage()));
        }
    }
}