<?php

namespace App\Models\estoque;

use App\Models\evento\EventosModel;
use Illuminate\Database\Eloquent\Model;

class ReservaEquipamentosModel extends Model
{
    protected $table = 'reserva_equipamentos';

    protected $fillable = [
        'item_id',
        'evento_id',
        'data_inicio',
        'data_fim',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function item()
    {
        return $this->belongsTo(ItensInventarioModel::class, 'item_id');
    }

    public function evento()
    {
        return $this->belongsTo(EventosModel::class, 'evento_id');
    }
}
