<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Jobs\ProcessOrder;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable; // Mais abrangente para capturar qualquer tipo de erro

class OrderController extends Controller
{
    public function index()
    {
        try {

            $orders = Cache::remember('orders', 60, function () {
                return Order::with('products')->get();
            });

            return response()->json($orders, 200);

        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro interno ao listar o pedido.', $e->getMessage(), 500);
        }
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Despacha o Job para a fila
            ProcessOrder::dispatch($validatedData, Auth::user()->id);

            return response()->json(['message' => 'Seu pedido está sendo processado.'], 202); // 

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro interno ao criar o pedido.', $e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {

            $order = Order::findOrFail($id);
            return response()->json($order, 201);

         } catch (ModelNotFoundException $e) {
            throw new ApiException('Pedido não encontrado.', $e->getMessage(), 404);
         } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro interno ao buscar o pedido.', $e->getMessage(), 500);
        }
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $id)
    {

        try {

            if(Order::findOrFail($id)->status === 'cancelado'){
              throw new ApiException('Não é possivel alterar o status de um pedido cancelado.','Faça um novo pedido.', 409);
            }

            DB::transaction(function () use ($request, $id) {
                $order = Order::findOrFail($id);
                $newStatus = $request->validated()['status'];

                // Devolve o estoque se o pedido for cancelado
                if ($newStatus === 'cancelado' && $order->status !== 'cancelado') {
                    foreach ($order->products as $product) {
                        Product::where('id', $product->id)->increment('stock', $product->pivot->quantity);
                    }
                }

                $order->status = $newStatus;
                $order->save();
            });

            Cache::forget('orders');
            return response()->json(['message' => 'Status do pedido atualizado com sucesso!', 'data' => Order::findOrFail($id)]);

        } catch (ModelNotFoundException $e) {
            throw new ApiException('Pedido não encontrado.', '', 404);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro interno ao atualizar o pedido.', $e->getMessage(), 500);
        }
    }

}
