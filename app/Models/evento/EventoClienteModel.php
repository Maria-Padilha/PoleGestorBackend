<?php

namespace App\Models\evento;

use App\Models\PessoasModel;
use Illuminate\Database\Eloquent\Model;

class EventoClienteModel extends Model
{
    protected $table = 'evento_cliente';

    protected $fillable = [
        'evento_id',
        'cliente_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function evento()
    {
        return $this->belongsTo(EventosModel::class, 'evento_id');
    }

    public function cliente()
    {
        return $this->belongsTo(PessoasModel::class, 'cliente_id');
    }
}
