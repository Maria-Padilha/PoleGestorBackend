<?php

namespace App\Models\evento;

use App\Models\EmpresaModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EventosModel extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'nome',
        'descricao',
        'data_inicio',
        'data_fim',
        'montagem',
        'desmontagem',
        'localizacao',
        'orcamento',
        'status',
        'tipo_evento',
        'repetir_evento',
        'usuario_id',
        'empresa_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function cliente()
    {
        return $this->hasMany(EventoClienteModel::class, 'evento_id');
    }

    public function funcionarios()
    {
        return $this->hasMany(EventoFuncionarioModel::class, 'evento_id');
    }

    public function kits()
    {
        return $this->hasMany(EventoKitModel::class, 'evento_id');
    }

    public function consumos()
    {
        return $this->hasMany(EventoItemConsumoModel::class, 'evento_id');
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresaModel::class, 'empresa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
