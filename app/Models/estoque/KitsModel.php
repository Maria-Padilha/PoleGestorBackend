<?php

namespace App\Models\estoque;

use App\Models\EmpresaModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class KitsModel extends Model
{
    protected $table = 'kits';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
        'usuario_id',
        'empresa_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function itens()
    {
        return $this->belongsToMany(ItensInventarioModel::class, 'kit_item', 'kit_id', 'item_id')
                    ->withPivot('quantidade')
                    ->withTimestamps();
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
