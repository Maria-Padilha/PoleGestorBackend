<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmpresaRequest extends FormRequest
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
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'required|string|max:255',
            'cnpj' => [
                'required',
                'string',
                'max:20',
                Rule::unique('empresas')->where(function ($query) {
                    return $query->where('id', $this->id);
                })->ignore($this->id)
            ],
            'tipo_empresa' => 'nullable|string|max:100',
            'telefone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'cep' => 'nullable|string|max:20',
            'numero' => 'nullable|string|max:20',
            'responsavel_id' => 'nullable|exists:users,id',
        ];
    }
}
