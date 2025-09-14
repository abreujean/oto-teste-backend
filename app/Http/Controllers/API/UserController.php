<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        try {

            $users = Cache::remember('users', 60, function () {
                return User::all();
            });

            return response()->json($users, 200);

        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro ao listar os usuários.', $e->getMessage(), 500);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {

            $validatedData = $request->validated();
            $user = User::create($validatedData);
            Cache::forget('users');

            return response()->json(['message' => 'Usuario ' . $user->name . ' criado com sucesso!'], 201);

        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro ao criar o usuário.', $e->getMessage(), 500);
        }
    }


    public function show(string $id): JsonResponse
    {
        try {

            $user = User::findOrFail($id);
            return response()->json($user, 201);

        } catch (ModelNotFoundException $e) {
            throw new ApiException('Usuário não encontrado.', $e->getMessage(), 404);    
        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro ao buscar o usuário.', $e->getMessage(), 500);
        }
    }


    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $validatedData = $request->validated();

            if (empty($validatedData['password'])) {
                unset($validatedData['password']);
            }

            $user->update($validatedData);
            Cache::forget('users');

           return response()->json(['message' => 'Usuário ' . $user->name . ' atualizado com sucesso!',
                'data' => $user], 200);

        } catch (ModelNotFoundException $e) {
            throw new ApiException('Usuário não encontrado.', $e->getMessage(), 404);    
        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro ao atualizar o usuário.', $e->getMessage(), 500);    
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {

            $user = User::findOrFail($id);

            if ($user->orders()->exists()) {
                throw new ApiException('Não é possível excluir o usuário, pois ele possui pedidos associados.', 'Unauthorized', 409);   
            }

            $user->delete();
            Cache::forget('users');
            
            return response()->json(['message' => 'Usuário excluído com sucesso.'], 200);

        } catch (ModelNotFoundException $e) {
            throw new ApiException('Usuário não encontrado.', $e->getMessage(), 404);   
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro ao deletar o usuário.', $e->getMessage(), 500);   
        }
    }

    public function orders(string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $orders = $user->orders;
             if ($orders->isEmpty()) {
                return response()->json(['message' => 'Nenhum pedido vinculado a esse usuário foi encontrado.'], 200);
            }
            return response()->json($orders, 200);
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Usuário não encontrado.', $e->getMessage(), 404);
        } catch (\Exception $e) {
            throw new ApiException('Ocorreu um erro ao buscar os pedidos do usuário.', $e->getMessage(), 500);
        }
    }

}