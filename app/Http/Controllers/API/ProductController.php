<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{

    public function index()
    {
        try {

            return Product::paginate(15);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocorreu um erro interno ao listar produto.'], 500);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {

            $product = Product::create($request->validated());
            return response()->json($product, 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocorreu um erro interno ao criar o produto.'], 500);
        }
    }

    public function show(string $id)
    {
        try {

            $product = Product::findOrFail($id);
            return response()->json($product, 201);

         } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->update($request->validated());

            return response()->json($product);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
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

            return response()->json(null, 204);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Produto não encontrado.'], 404);
        }
    }
}
