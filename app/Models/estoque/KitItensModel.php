<?php

namespace App\Models\estoque;

use Illuminate\Database\Eloquent\Model;

class KitItensModel extends Model
{
    protected $table = 'kit_item';

    protected $fillable = [
        'kit_id',
        'item_id',
        'quantidade',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function kit()
    {
        return $this->belongsTo(KitsModel::class, 'kit_id');
    }

    public function item()
    {
        return $this->belongsTo(ItensInventarioModel::class, 'item_id');
    }
}
