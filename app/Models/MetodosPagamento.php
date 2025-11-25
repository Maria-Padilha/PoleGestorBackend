<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodosPagamento extends Model
{
    protected $table = 'metodos_pagamento';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'token_gateway',
        'bandeira',
        'ultimo_digitos',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
