<?php

namespace App\Models\evento;

use App\Models\estoque\ItensInventarioModel;
use Illuminate\Database\Eloquent\Model;

class EventoItemConsumoModel extends Model
{
    protected $table = 'evento_item_consumo';

    protected $fillable = [
        'evento_id',
        'item_id',
        'quantidade_consumida',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function evento()
    {
        return $this->belongsTo(EventosModel::class, 'evento_id');
    }

    public function itemConsumo()
    {
        return $this->belongsTo(ItensInventarioModel::class, 'item_id');
    }
}
