<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanosModel extends Model
{
    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'descricao',
        'periodicidade_dias',
        'preco_total',
        'preco_mensal',
        'beneficios',
        'nivel',
        'ativo',
    ];

    protected $appends = [
        'economia_percentual',
        'economia_reais',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getPrecoTotalAttribute($value)
    {
        return number_format((float)$value, 2, ',', '.');
    }

    public function getPrecoMensalAttribute($value)
    {
        return number_format((float)$value, 2, ',', '.');
    }


    public function getEconomiaReaisAttribute()
    {
        $planoMensal = PlanosModel::where('nivel', 1)->first();

        if (!$planoMensal) {
            return 0;
        }

        $precoMensal = floatval(str_replace(',', '.', $planoMensal->preco_mensal));
        $periodicidade = floatval($this->periodicidade_dias);
        $precoTotal = floatval(str_replace(',', '.', $this->preco_total));

        $economia = round(
            ($precoMensal * ($periodicidade / 30)) - $precoTotal,
            2
        );

        return number_format($economia, 2, ',', '.');
    }

    public function getEconomiaPercentualAttribute()
    {
        // pega o plano base (mensal) - pode ser pelo nivel ou pelo menor período
        $planoBase = self::orderBy('periodicidade_dias')->first();

        if (!$planoBase || !$planoBase->preco_mensal || !$this->preco_mensal) {
            return 0;
        }

        // se for o próprio plano base (mensal), economia = 0
        if ($planoBase->id === $this->id) {
            return 0;
        }

        $precoMensalBase = (float) $planoBase->preco_mensal;
        $precoMensalPlano = (float) $this->preco_mensal;

        // diferença de preço em relação ao mensal
        $diferenca = $precoMensalBase - $precoMensalPlano;

        // economia em %
        $economia = ($diferenca / $precoMensalBase) * 100;

        return round($economia);
    }
}
