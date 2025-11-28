<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoasModel extends Model
{
    protected $table = 'pessoas';

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'genero',
        'pessoalidade',
        'data_nascimento',
        'telefone',
        'email',

        'usuario_id',
        'empresa_id',

        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'complemento',

        'ativo',
        'tipo_pessoa',
        'observacoes',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresaModel::class, 'empresa_id');
    }
}
