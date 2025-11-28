<?php

namespace App\Models\evento;

use App\Models\PessoasModel;
use Illuminate\Database\Eloquent\Model;

class EventoFuncionarioModel extends Model
{
    protected $table = 'evento_funcionario';

    protected $fillable = [
        'evento_id',
        'funcionario_id',
        'funcao',
        'horas_trabalhadas',
        'custo',
        'observacoes',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function evento()
    {
        return $this->belongsTo(EventosModel::class, 'evento_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(PessoasModel::class, 'funcionario_id');
    }
}
