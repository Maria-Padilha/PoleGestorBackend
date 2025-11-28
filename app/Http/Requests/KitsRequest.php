<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KitsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'required|boolean',
            'itens' => 'required|array',
            'itens.*.item_id' => 'required|integer|exists:itens_inventario,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'usuario_id' => 'nullable|exists:usuarios,id',
            'empresa_id' => 'nullable|exists:empresas,id',
        ];
    }
}
