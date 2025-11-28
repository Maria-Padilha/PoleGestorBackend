<?php

namespace App\Http\Controllers\estoque;

use App\Http\Controllers\BaseController;
use App\Models\estoque\ItensInventarioModel;
use App\Models\estoque\MovimentacoesEstoqueModel;
use App\Services\EstoqueService;
use Illuminate\Http\Request;

class MovimentacaoEstoqueController extends BaseController
{
    protected EstoqueService $service;

    public function movimentar(Request $request)
    {
        // Valores iniciam como null
        $usuarioId = null;
        $empresaId = null;

        /*
        |--------------------------------------------------------------------------
        | USUÁRIO MASTER
        |--------------------------------------------------------------------------
        */
        if ($this->userMaster->master === true) {
            $usuarioId = $this->userMaster->id;
            $empresaId = $this->empresa->id;
        }

        /*
        |--------------------------------------------------------------------------
        | COLABORADOR (NÃO MASTER)
        |--------------------------------------------------------------------------
        */
        else {

            if (!$this->permissoes || !$this->permissoes->gerenciar_estoque) {
                return response()->json([
                    'message' => 'Você não tem permissão para registrar movimentações de estoque.'
                ], 403);
            }

            $usuarioId = $this->empresa->responsavel_id;
            $empresaId = $this->colaborador->empresa_id;
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÃO DO REQUEST (somente os campos do formulário)
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'item_id' => 'required|exists:itens_inventario,id',
            'tipo_movimentacao' => 'required|in:entrada,saida,ajuste',
            'quantidade' => 'required|numeric|min:0.01',
            'origem' => 'nullable|string',
            'evento_id' => 'nullable|exists:eventos,id',
            'observacoes' => 'nullable|string'
        ]);

        /*
        |--------------------------------------------------------------------------
        | BUSCA DO ITEM
        |--------------------------------------------------------------------------
        */
        $item = ItensInventarioModel::findOrFail($validated['item_id']);

        /*
        |--------------------------------------------------------------------------
        | EXECUTA A MOVIMENTAÇÃO
        |--------------------------------------------------------------------------
        */
        $mov = $this->service->movimentar(
            usuarioId: $usuarioId,
            empresaId: $empresaId,
            item: $item,
            tipo: $validated['tipo_movimentacao'],
            quantidade: $validated['quantidade'],
            origem: $validated['origem'] ?? null,
            eventoId: $validated['evento_id'] ?? null,
            observacoes: $validated['observacoes'] ?? null
        );

        return response()->json([
            'message' => 'Movimentação registrada com sucesso',
            'movimentacao' => $mov
        ], 201);
    }

    public function listar()
    {
        $movimentacoes = null;
        $message = '';

        if ($this->userMaster->master !== true) {
            $message = 'Lista de movimentações de estoque da empresa';
            $movimentacoes = MovimentacoesEstoqueModel::where('empresa_id', $this->empresa->id)
                ->with(['item', 'evento'])
                ->orderByDesc('id')
                ->get();
        } else {
            $message = 'Lista de movimentações de estoque do usuário master';
            $movimentacoes = MovimentacoesEstoqueModel::where('usuario_id', $this->userMaster->id)
                ->with(['item', 'evento'])
                ->orderByDesc('id')
                ->get();
        }

        return response()->json([
            'message' => $message,
            'data' => $movimentacoes
        ], 200);
    }
}
