<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
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
            // DB::transaction garante que tudo abaixo seja executado em uma transação.
            // Ele faz o commit automaticamente no sucesso, ou rollback em caso de erro.
            $order = DB::transaction(function () use ($request) {
                $validatedData = $request->validated();
                $totalAmount = 0;

                // Valida o estoque e calcula o total
                foreach ($validatedData['products'] as $productData) {
                    $product = Product::findOrFail($productData['product_id']);

                    if ($product->stock < $productData['quantity']) {
                         throw new ApiException('Produto ' . $product->name . ' não tem estoque suficiente.', 'Reponha o estoques', 409);
                    }

                    $totalAmount += $product->price * $productData['quantity'];
                }

                // Cria o pedido
                $order = Order::create([
                    'user_id' => Auth::user()->id, // Provisório até implementarmos autenticação
                    'total_amount' => $totalAmount,
                    'status' => 'pendente',
                ]);

                // Vincula os produtos ao pedido e atualiza o estoque
                foreach ($validatedData['products'] as $productData) {
                    $product = Product::findOrFail($productData['product_id']);
                    
                    $order->products()->attach($productData['product_id'], [
                        'quantity' => $productData['quantity'],
                        'price' => $product->price
                    ]);

                    $product->decrement('stock', $productData['quantity']);
                }

                return $order;
            });
            Cache::forget('orders');
            return response()->json(['message'=> 'Pedido criado com sucesso!', $order->load('products')], 201);

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
