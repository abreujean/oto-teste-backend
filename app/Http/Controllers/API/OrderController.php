<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable; // Mais abrangente para capturar qualquer tipo de erro

class OrderController extends Controller
{
    public function index()
    {
        try {

            $order = Order::all();
            return response()->json($order, 200);

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
                         throw new ApiException('Produto ' . $product->name . ' não tem estoque suficiente.', 'Reponha o estoques', 400);
                    }

                    $totalAmount += $product->price * $productData['quantity'];
                }

                // Cria o pedido
                $order = Order::create([
                    'user_id' => 1, // Provisório até implementarmos autenticação
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

            return response()->json(['message'=> 'Pedido criado com sucesso!', $order->load('products')], 201);

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
            $order = Order::findOrFail($id);
            $order->status = $request->validated()['status'];
            $order->save();

            return response()->json($order);
        
        }  catch (ModelNotFoundException $e) {
            throw new ApiException('Pedido não encontrado.', $e->getMessage(), 404);
        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro interno ao atualizar o pedido.', $e->getMessage(), 500);
        }
    }

}
