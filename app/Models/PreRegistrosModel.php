<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreRegistrosModel extends Model
{
    protected $table = 'pre_registros';
    protected $fillable = [
        'nome',
        'email',
        'cpf_cnpj',
        'telefone',
        'nascimento',
        'token',
        'token_expires_at',
    ];
}
