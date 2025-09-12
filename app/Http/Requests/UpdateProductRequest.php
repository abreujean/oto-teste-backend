<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Alterado para true para permitir a requisição
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     *  Prepara os dados para validação.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $this->merge([
                'price' => str_replace(',', '.', $this->price),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
        ];
    }

}
