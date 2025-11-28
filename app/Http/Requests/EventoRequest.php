<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventoRequest extends FormRequest
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
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'montagem' => 'nullable|date',
            'desmontagem' => 'nullable|date',
            'localizacao' => 'nullable|string|max:255',
            'orcamento' => 'nullable|numeric|min:0',
            'status' => 'required|in:planejado,em_andamento,concluido,cancelado',
            'tipo_evento' => 'nullable|string|max:100',
            'repetir_evento' => 'required|boolean',
            'usuario_id' => 'nullable|exists:usuarios,id',
            'empresa_id' => 'nullable|exists:empresas,id',

            // Arrays
            'clientes' => 'nullable|array',
            'funcionarios' => 'nullable|array',
            'kits' => 'nullable|array',
            'evento_item_consumo' => 'nullable|array',
        ];
    }
}
