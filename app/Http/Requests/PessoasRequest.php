<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PessoasRequest extends FormRequest
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
            'cpf_cnpj' => [
                'required',
                'string',
                'max:14',
                Rule::unique('pessoas')->where(function ($query) {
                    return $query->where('id', $this->id);
                })->ignore($this->id)
            ],
            'genero' => 'nullable|string|in:masculino,feminino,outro',
            'pessoalidade' => 'required|string|in:fisica,juridica',
            'data_nascimento' => 'nullable|date',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'usuario_id' => 'nullable|exists:usuarios,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'complemento' => 'nullable|string|max:255',
            'ativo' => 'required|boolean',
            'tipo_pessoa' => 'required|string|in:funcionario,terceirizado,cliente,fornecedor',
            'observacoes' => 'nullable|string',
        ];
    }
}
