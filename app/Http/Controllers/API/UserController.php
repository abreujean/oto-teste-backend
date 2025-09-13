<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        try {

            $users = User::all();
            return response()->json($users, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocorreu um erro ao listar os usuários.',
                'error' => $e->getMessage()], 500);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {

            $validatedData = $request->validated();
            $validatedData['password'] = Hash::make($validatedData['password']);

            $user = User::create($validatedData);

            return response()->json(['message' => 'Usuário ' . $user->name . ' criado com sucesso!'], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocorreu um erro ao criar o usuário.',
            'error' => $e->getMessage()], 500);
        }
    }


    public function show(string $id): JsonResponse
    {
        try {

            $user = User::findOrFail($id);
            return response()->json($user, 201);

        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
         } catch (\Exception $e) {
            return response()->json(['message' => 'Ocorreu um erro ao buscar o usuário.',
        'error' => $e->getMessage()], 500);
        }
    }


    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $validatedData = $request->validated();

            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $user->update($validatedData);

           return response()->json(['message' => 'Usuário ' . $user->name . ' atualizado com sucesso!',
                'data' => $user], 200);

        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocorreu um erro ao atualizar o usuário.',
            'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {

            $user = User::findOrFail($id);

            if ($user->orders()->exists()) {
                return response()->json(['message' => 'Não é possível excluir o usuário, pois ele possui pedidos associados.'], 409); // 409 Conflict
            }

            $user->delete();

            return response()->json(['message' => 'Usuário excluído com sucesso.'], 200);

        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocorreu um erro ao deletar o usuário.',
            'error' => $e->getMessage()], 500);
        }
    }

}