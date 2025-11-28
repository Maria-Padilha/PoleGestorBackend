<?php

namespace App\Services;

use App\Models\estoque\ItensInventarioModel;
use App\Models\estoque\MovimentacoesEstoqueModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstoqueService
{
    public function movimentar(
        ?int                 $usuarioId,
        ?int                 $empresaId,
        ItensInventarioModel $item,
        string               $tipo,
        float                $quantidade,
        ?string              $origem = null,
        ?int                 $eventoId = null,
        ?string              $observacoes = null
    )
    {
        return DB::transaction(function () use ($usuarioId, $empresaId, $item, $tipo, $quantidade, $origem, $eventoId, $observacoes) {

            /*
            |--------------------------------------------------------------------------
            | 1. REGRAS PARA EQUIPAMENTOS
            |--------------------------------------------------------------------------
            */
            if ($item->tipo === 'equipamento') {

                throw ValidationException::withMessages([
                    'item' => 'Equipamentos não permitem movimentação de estoque. Use status: em_uso / manutencao / disponivel.'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 2. REGRAS PARA CONSUMÍVEIS
            |--------------------------------------------------------------------------
            */

            if ($tipo === 'entrada') {
                $item->quantidade_atual += $quantidade;
            } elseif ($tipo === 'saida') {

                if ($item->quantidade_atual == 0) {
                    throw ValidationException::withMessages([
                        'quantidade' => 'Estoque zerado. Não é possível realizar saída.'
                    ]);
                }

                if ($item->quantidade_atual < $quantidade) {
                    throw ValidationException::withMessages([
                        'quantidade' => 'Quantidade insuficiente em estoque.'
                    ]);
                }

                $item->quantidade_atual -= $quantidade;
            } elseif ($tipo === 'ajuste') {
                if ($quantidade < 0) {
                    throw ValidationException::withMessages([
                        'quantidade' => 'O ajuste não pode deixar o estoque negativo.'
                    ]);
                }

                $item->quantidade_atual = $quantidade;
            }

            $item->save();

            /*
            |--------------------------------------------------------------------------
            | 3. Registrar a movimentação
            |--------------------------------------------------------------------------
            */

            return MovimentacoesEstoqueModel::create([
                'usuario_id' => $usuarioId,
                'empresa_id' => $empresaId,
                'item_id' => $item->id,
                'tipo_movimentacao' => $tipo,
                'quantidade' => $quantidade,
                'origem' => $origem,
                'evento_id' => $eventoId,
                'observacoes' => $observacoes
            ]);
        });
    }
}
