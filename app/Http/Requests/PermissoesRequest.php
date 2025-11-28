<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissoesRequest extends FormRequest
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
            'colaborador_id' => 'nullable|exists:colaboradores,id',
            'gerenciar_pessoas' => 'nullable|boolean',
            'gerenciar_eventos' => 'nullable|boolean',
            'gerenciar_financeiro' => 'nullable|boolean',
            'gerenciar_relatorios' => 'nullable|boolean',
            'gerenciar_estoque' => 'nullable|boolean',
            'gerenciar_inventario' => 'nullable|boolean',
            'gerenciar_contratos' => 'nullable|boolean',
        ];
    }
}
