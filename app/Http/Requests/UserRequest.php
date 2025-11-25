<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('id', $this->id);
                })->ignore($this->id)
            ],
            'senha' => 'nullable|string|min:8|confirmed',
            'cpf_cnpj' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('id', $this->id);
                })->ignore($this->id)
            ],
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'nascimento' => 'nullable|date',
            'cep' => 'nullable|string|max:10',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'plano_id' => 'nullable|exists:planos,id',
        ];
    }
}
