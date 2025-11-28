<?php

namespace App\Models\estoque;

use App\Models\EmpresaModel;
use App\Models\evento\EventosModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MovimentacoesEstoqueModel extends Model
{
    protected $table = 'movimentacoes_estoque';

    protected $fillable = [
        'item_id',
        'tipo_movimentacao',
        'quantidade',
        'origem',
        'evento_id',
        'observacoes',
        'usuario_id',
        'empresa_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y H:i:s',
        'updated_at' => 'datetime:d-m-Y H:i:s',
    ];

    public function item()
    {
        return $this->belongsTo(ItensInventarioModel::class, 'item_id');
    }

    public function evento()
    {
        return $this->belongsTo(EventosModel::class, 'evento_id');
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
