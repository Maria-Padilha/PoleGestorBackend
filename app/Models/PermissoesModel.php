<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissoesModel extends Model
{
    protected $table = 'permissoes';

    protected $fillable = [
        'colaborador_id',
        'gerenciar_pessoas',
        'gerenciar_eventos',
        'gerenciar_financeiro',
        'gerenciar_relatorios',
        'gerenciar_estoque',
        'gerenciar_inventario',
        'gerenciar_contratos',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function colaborador()
    {
        return $this->belongsTo(ColaboradoresModel::class, 'colaborador_id');
    }
}
