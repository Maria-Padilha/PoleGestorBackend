<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColaboradoresModel extends Model
{
    protected $table = 'colaboradores';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'funcao',
        'permissoes',
        'ativo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresaModel::class, 'empresa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function permissoes()
    {
        return $this->hasOne(PermissoesModel::class, 'colaborador_id');
    }
}
