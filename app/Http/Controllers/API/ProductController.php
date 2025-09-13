<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{

    public function index()
    {
        try {

            $product = Product::all();
            return response()->json($product, 200);

        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Ocorreu um erro interno ao listar produto.',
                'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {

            $product = Product::create($request->validated());
            return response()->json(['message' => 'Produto ' . $product->name .  ' criado com sucesso!'], 201);

        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Ocorreu um erro interno ao criar o produto.',
                'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {

            $product = Product::findOrFail($id);
            return response()->json($product, 200);

         } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
         } catch (\Exception $e) {
            return response()->json( ['message' => 'Ocorreu um erro interno ao buscar o produto.',
                'error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->update($request->validated());

            return response()->json(['message' => 'Produto ' . $product->name .  ' foi atualizado sucesso!'], 201);

        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Produto não encontrado.'], 404); 
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocorreu um erro interno ao atualizar o produto.',
            'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->orders()->exists()) {
                return response()->json(['message' => 'Não é possível excluir um produto que está vinculado a um pedido.'], 422);
            }

            $product->delete();

            return response()->json(['message' => 'Produto foi excluido com sucesso!'], 200);

        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Ocorreu um erro interno ao excluir o produto.',
                'error' => $e->getMessage()], 500);
        }
    }
}
