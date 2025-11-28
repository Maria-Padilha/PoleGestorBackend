<?php

namespace App\Models\evento;

use App\Models\estoque\KitsModel;
use Illuminate\Database\Eloquent\Model;

class EventoKitModel extends Model
{
    protected $table = 'evento_kit';

    protected $fillable = [
        'evento_id',
        'kit_id',
        'quantidade',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function evento()
    {
        return $this->belongsTo(EventosModel::class, 'evento_id');
    }

    public function kit()
    {
        return $this->belongsTo(KitsModel::class, 'kit_id');
    }
}
