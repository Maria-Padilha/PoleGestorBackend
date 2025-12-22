<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItensInventarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function getItemId()
    {
        return $this->route('itens_inventario') ?? $this->id;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('itens_inventario');

        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'codigo' => [
                'required',
                Rule::unique('itens_inventario')->where(function ($query) {
                    return $query->where('id', $this->id);
                })->ignore($id)
            ],
            'tipo' => 'required|string|max:100|in:consumivel,equipamento',
            'unidade' => 'required|string|max:50',
            'controla_estoque' => 'required|boolean',
            'quantidade_atual' => 'nullable|numeric',
            'quantidade_total' => 'nullable|numeric',
            'quantidade_em_uso' => 'nullable|numeric',
            'quantidade_disponivel' => 'nullable|numeric',
            'status' => 'nullable|in:disponivel,em_uso,manutencao,esgotado,acabando,perdido,em_estoque',
            'localizacao' => 'nullable|string|max:255',
            'fornecedor' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'usuario_id' => 'nullable|exists:usuarios,id',
            'empresa_id' => 'nullable|exists:empresas,id',
        ];
    }
}
