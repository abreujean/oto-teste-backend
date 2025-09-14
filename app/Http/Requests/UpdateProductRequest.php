<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'name' => ['sometimes','required','string','max:255','regex:/^(?=.*[a-zA-ZÀ-ÿ])[a-zA-ZÀ-ÿ0-9\s.]+$/u'],
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
        ];
    }

     public function messages(): array
    {
        return [
            'name.regex' => 'O campo nome deve conter pelo menos uma letra.',
            'name.required' => 'O campo nome invalido.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
        ], 422));
    }

}
