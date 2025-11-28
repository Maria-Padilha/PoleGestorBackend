<?php

namespace App\Models\estoque;

use App\Models\EmpresaModel;
use App\Models\evento\EventosModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ItensInventarioModel extends Model
{
    protected $table = 'itens_inventario';

    protected $fillable = [
        'usuario_id',
        'empresa_id',
        'nome',
        'descricao',
        'codigo',
        'tipo',
        'categoria',
        'unidade',
        'controla_estoque',
        'quantidade_atual',
        'quantidade_total',
        'quantidade_em_uso',
        'quantidade_disponivel',
        'status',
        'localizacao',
        'fornecedor',
        'observacoes',
    ];

    protected $appends = [
        'estoque',
        'status_legivel',
        'quantidade_reservada',
        'porcentagem_uso',
        'porcentagem_disponivel',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'quantidade_atual',
        'quantidade_total',
        'quantidade_em_uso',
        'quantidade_disponivel',
        'status',
        'controla_estoque',
    ];

    public function getEstoqueAttribute()
    {
        if ($this->tipo === 'consumivel') {
            return [
                'quantidade_atual' => $this->quantidade_atual,
                'status' => $this->status,
            ];
        }

        if ($this->tipo === 'equipamento') {
            return [
                'quantidade_total' => $this->quantidade_total,
                'quantidade_em_uso' => $this->quantidade_em_uso,
                'quantidade_disponivel' => $this->quantidade_disponivel,
                'status' => $this->status,
            ];
        }

        return []; // caso no futuro tenha mais tipos
    }

    public function getStatusLegivelAttribute()
    {
        return match ($this->status) {
            'disponivel' => 'Disponível',
            'em_uso' => 'Em Uso',
            'manutencao' => 'Em Manutenção',
            'esgotado' => 'Esgotado',
            'acabando' => 'Atenção: Estoque Baixo',
            default => 'Cadastrado para Uso',
        };
    }

    public function getQuantidadeReservadaAttribute()
    {
        return $this->reservas()->count();
    }

    public function movimentacoes()
    {
        return $this->hasMany(MovimentacoesEstoqueModel::class, 'item_id');
    }

    public function kits()
    {
        return $this->belongsToMany(KitsModel::class, 'kit_item', 'item_id', 'kit_id')
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

    public function eventos()
    {
        return $this->belongsToMany(EventosModel::class, 'evento_kit', 'item_id', 'evento_id')
            ->withPivot('quantidade')
            ->withTimestamps();
    }

    public function reservas()
    {
        return $this->hasMany(ReservaEquipamentosModel::class, 'item_id');
    }

    public function getPorcentagemDisponivelAttribute()
    {
        if ($this->tipo === 'equipamento' && $this->quantidade_total > 0) {
            return round(($this->quantidade_disponivel / $this->quantidade_total) * 100, 2);
        }

        if ($this->tipo === 'consumivel' && $this->quantidade_total > 0) {
            return round(($this->quantidade_atual / $this->quantidade_total) * 100, 2);
        }

        return 0;
    }

    public function getPorcentagemUsoAttribute()
    {
        if ($this->tipo === 'equipamento' && $this->quantidade_total > 0) {
            return round(($this->quantidade_em_uso / $this->quantidade_total) * 100, 2);
        }

        return 0;
    }
}
