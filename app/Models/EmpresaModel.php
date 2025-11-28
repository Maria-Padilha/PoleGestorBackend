<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaModel extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'tipo_empresa',
        'telefone',
        'email',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'numero',
        'responsavel_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function colaboradores()
    {
        return $this->hasMany(ColaboradoresModel::class, 'empresa_id');
    }
}
