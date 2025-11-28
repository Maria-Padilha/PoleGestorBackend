<?php

namespace App\Http\Controllers\evento;

use App\Http\Controllers\BaseController;
use App\Http\Requests\EventoRequest;
use App\Models\estoque\ItensInventarioModel;
use App\Models\estoque\KitsModel;
use App\Models\estoque\ReservaEquipamentosModel;
use App\Models\evento\EventoClienteModel;
use App\Models\evento\EventoFuncionarioModel;
use App\Models\evento\EventoItemConsumoModel;
use App\Models\evento\EventoKitModel;
use App\Models\evento\EventosModel;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventoController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eventos = null;
        $message = '';

        if ($this->userMaster->master === true) {
            $message = 'Lista de eventos do usuário master';
            $eventos = EventosModel::where('usuario_id', $this->userMaster->id)
                ->with('cliente', 'funcionarios', 'kits', 'kits.kit', 'consumos')
                ->get();

        } else {
            $message = 'Lista de eventos da empresa';
            $eventos = EventosModel::where('empresa_id', $this->empresa->id)
                ->with('cliente', 'funcionarios', 'kits', 'kits.kit', 'consumos')
                ->get();
        }

        return response()->json([
            'message' => $message,
            'data' => $eventos
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventoRequest $request)
    {

        $data = null;

        if ($this->userMaster->master !== true) {
            if (!$this->permissoes || !$this->permissoes->gerenciar_eventos) {
                return response()->json([
                    'message' => 'Você não tem permissão para cadastrar eventos.'
                ], 403);
            }

            $data = array_merge($request->validated(), [
                'usuario_id' => $this->empresa->responsavel_id,
                'empresa_id' => $this->colaborador->empresa_id,
            ]);
        } else {
            $data = array_merge($request->validated(), [
                'usuario_id' => $this->userMaster->id,
                'empresa_id' => $this->empresa->id,
            ]);
        }

        $evento = EventosModel::create($data);

        // clientes
        if ($request->clientes) {
            foreach ($request->clientes as $c) {
                EventoClienteModel::create([
                    'evento_id' => $evento->id,
                    'cliente_id' => $c['cliente_id'],
                ]);
            }
        }

        // funcionários
        if ($request->funcionarios) {
            foreach ($request->funcionarios as $f) {
                EventoFuncionarioModel::create([
                    'evento_id' => $evento->id,
                    'funcionario_id' => $f['funcionario_id'],
                    'funcao' => $f['funcao'] ?? null,
                    'horas_trabalhadas' => $f['horas_trabalhadas'] ?? 0,
                    'custo' => $f['custo'] ?? 0,
                    'observacoes' => $f['observacoes'] ?? null,
                ]);
            }
        }

        // kits
        if ($request->kits) {
            foreach ($request->kits as $k) {
                EventoKitModel::create([
                    'evento_id' => $evento->id,
                    'kit_id' => $k['kit_id'],
                    'quantidade' => $k['quantidade'] ?? 1,
                ]);
            }
        }

        // consumo
        if ($request->itens_consumo) {
            foreach ($request->itens_consumo as $i) {
                EventoItemConsumoModel::create([
                    'evento_id' => $evento->id,
                    'item_id' => $i['item_id'],
                    'quantidade_consumida' => $i['quantidade_consumida'],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RESERVAR ITENS DO KIT SE STATUS = iniciado | em_andamento
        |--------------------------------------------------------------------------
        */
        if (in_array($evento->status, ['planejado', 'em_andamento'])) {
            $this->reservarItensDoKit($evento);
        }

        return response()->json([
            'message' => 'Evento cadastrado com sucesso',
            'evento' => $evento
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $evento = EventosModel::with('cliente', 'funcionarios', 'kits', 'itensConsumidos')->findOrFail($id);
        return response()->json([
            'message' => 'Dados recuperados com sucesso.',
            'data' => $evento
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventoRequest $request, string $id)
    {
        if ($this->userMaster->master !== true) {
            if (!$this->permissoes || !$this->permissoes->gerenciar_eventos) {
                return response()->json([
                    'message' => 'Você não tem permissão para atualizar eventos.'
                ], 403);
            }
        }

        $evento = EventosModel::findOrFail($id);

        $statusAnterior = $evento->status;

        // Atualiza o evento
        $evento->update($request->validated());

        // 🔄 se estava em andamento e mudou para rascunho → devolve itens
        if (in_array($statusAnterior, ['iniciado', 'em_andamento']) &&
            !in_array($evento->status, ['iniciado', 'em_andamento'])) {

            $this->devolverItensDoKit($evento);
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENTES
        |--------------------------------------------------------------------------
        */
        EventoClienteModel::where('evento_id', $evento->id)->delete();

        if ($request->cliente) {
            foreach ($request->cliente as $c) {
                EventoClienteModel::create([
                    'evento_id' => $evento->id,
                    'cliente_id' => $c['cliente_id'],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FUNCIONÁRIOS
        |--------------------------------------------------------------------------
        */
        EventoFuncionarioModel::where('evento_id', $evento->id)->delete();

        if ($request->funcionarios) {
            foreach ($request->funcionarios as $f) {
                EventoFuncionarioModel::create([
                    'evento_id' => $evento->id,
                    'funcionario_id' => $f['funcionario_id'],
                    'funcao' => $f['funcao'] ?? null,
                    'horas_trabalhadas' => $f['horas_trabalhadas'] ?? 0,
                    'custo' => $f['custo'] ?? 0,
                    'observacoes' => $f['observacoes'] ?? null,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | KITS
        |--------------------------------------------------------------------------
        */
        EventoKitModel::where('evento_id', $evento->id)->delete();

        if ($request->kits) {
            foreach ($request->kits as $k) {
                EventoKitModel::create([
                    'evento_id' => $evento->id,
                    'kit_id' => $k['kit_id'],
                    'quantidade' => $k['quantidade'] ?? 1,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CONSUMO
        |--------------------------------------------------------------------------
        */
        EventoItemConsumoModel::where('evento_id', $evento->id)->delete();

        if ($request->itens_consumo) {
            foreach ($request->itens_consumo as $i) {
                EventoItemConsumoModel::create([
                    'evento_id' => $evento->id,
                    'item_id' => $i['item_id'],
                    'quantidade_consumida' => $i['quantidade_consumida'],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RESERVAR ITENS DO KIT SE STATUS = iniciado | em_andamento
        |--------------------------------------------------------------------------
        */
        if (in_array($evento->status, ['planejado'])) {
            $this->reservarItensDoKit($evento);
        }

        if (in_array($evento->status, ['iniciado', 'em_andamento', 'confirmado'])) {
            $this->ativarItensReservados($evento);
        }

        return response()->json([
            'message' => 'Evento atualizado com sucesso',
            'evento' => $evento
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($this->userMaster->master !== true) {
            if (!$this->permissoes || !$this->permissoes->gerenciar_eventos) {
                return response()->json([
                    'message' => 'Você não tem permissão para remover itens de inventário.'
                ], 403);
            }
        }

        $evento = EventosModel::findOrFail($id);
        $evento->delete();

        return response()->json(['message' => 'Evento excluído com sucesso'], 200);
    }

    public function finalizarEvento(
        $id,
        Request $request,
        EstoqueService $estoqueService
    ) {
        $usuarioId = $this->userMaster->master ? $this->userMaster->id : $this->empresa->responsavel_id;
        $empresaId = $this->userMaster->master ? $this->empresa->id : $this->colaborador->empresa_id;

        $request->validate([
            'evento_item_consumo' => 'required|array',
            'evento_item_consumo.*.item_id' => 'required|exists:itens_inventario,id',
            'evento_item_consumo.*.quantidade_consumida' => 'required|numeric|min:0',
        ]);

        $evento = EventosModel::findOrFail($id);

        DB::transaction(function () use ($request, $evento, $usuarioId, $empresaId, $estoqueService) {

            foreach ($request->evento_item_consumo as $consumo) {

                $item = ItensInventarioModel::findOrFail($consumo['item_id']);
                $qtd = floatval($consumo['quantidade_consumida']);

                // 🔹 Registrar consumo
                EventoItemConsumoModel::create([
                    'evento_id' => $evento->id,
                    'item_id' => $item->id,
                    'quantidade_consumida' => $qtd,
                ]);

                /*
                |--------------------------------------------------------------------------
                | 1. CONSUMÍVEL → Baixa estoque
                |--------------------------------------------------------------------------
                */
                if ($item->tipo === 'consumivel' && $item->controla_estoque) {

                    if ($qtd > 0) {
                        $estoqueService->movimentar(
                            usuarioId: $usuarioId,
                            empresaId: $empresaId,
                            item: $item,
                            tipo: 'saida',
                            quantidade: $qtd,
                            origem: 'evento',
                            eventoId: $evento->id,
                            observacoes: "Consumo no evento: {$evento->nome}"
                        );
                    }

                    // atualizar status
                    if ($item->quantidade_atual == 0) {
                        $item->status = 'esgotado';
                    } elseif ($item->quantidade_atual <= 5) {
                        $item->status = 'acabando';
                    } else {
                        $item->status = 'em_estoque';
                    }

                    $item->save();
                }

                /*
                |--------------------------------------------------------------------------
                | 2. EQUIPAMENTO → Devolver unidade
                |--------------------------------------------------------------------------
                */
                if ($item->tipo === 'equipamento') {

                    if ($item->quantidade_em_uso > 0) {
                        $item->quantidade_em_uso -= 1;
                        $item->quantidade_disponivel += 1;
                    }

                    if ($item->quantidade_em_uso == 0) {
                        $item->status = 'disponivel';
                    }

                    $item->save();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. DEVOLVER TODOS EQUIPAMENTOS DE KITS
            |--------------------------------------------------------------------------
            */
            $this->devolverItensDoKit($evento);

            /*
            |--------------------------------------------------------------------------
            | 4. REMOVER RESERVAS DO BANCO
            |--------------------------------------------------------------------------
            */
            $this->removerReservasDoEvento($evento->id);

            /*
            |--------------------------------------------------------------------------
            | 5. ATUALIZAR STATUS DO EVENTO
            |--------------------------------------------------------------------------
            */
            $evento->status = 'concluido';
            $evento->save();
        });

        return response()->json([
            'message' => 'Evento finalizado com sucesso.',
            'evento' => $evento->load('consumos.itemConsumo'),
        ]);
    }

    private function reservarItensDoKit(EventosModel $evento)
    {
        $kits = EventoKitModel::where('evento_id', $evento->id)->get();

        foreach ($kits as $kitEvento) {

            $kit = KitsModel::with('itens')->find($kitEvento->kit_id);

            foreach ($kit->itens as $item) {

                if ($item->tipo === 'equipamento') {

                    /*
                    |--------------------------------------------------------------------------
                    | 1 — Contar reservas existentes no mesmo período
                    |--------------------------------------------------------------------------
                    */
                    $reservasNoPeriodo = ReservaEquipamentosModel::where('item_id', $item->id)
                        ->where(function ($q) use ($evento) {
                            $q->whereBetween('data_inicio', [$evento->data_inicio, $evento->data_fim])
                                ->orWhereBetween('data_fim', [$evento->data_inicio, $evento->data_fim]);
                        })
                        ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | 2 — Verificar se ainda há disponibilidade
                    |--------------------------------------------------------------------------
                    |
                    | Se o número de reservas para o mesmo horário >= quantidade_total,
                    | significa que todos já estão reservados e NÃO podemos reservar outro.
                    |
                    */
                    if ($reservasNoPeriodo >= $item->quantidade_total) {
                        throw ValidationException::withMessages([
                            'equipamento' => "O item {$item->nome} já atingiu o limite de reservas para esse horário."
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 3 — Criar a reserva (AQUI AINDA NÃO MUDA EM USO!)
                    |--------------------------------------------------------------------------
                    */
                    ReservaEquipamentosModel::create([
                        'item_id' => $item->id,
                        'evento_id' => $evento->id,
                        'data_inicio' => $evento->data_inicio,
                        'data_fim' => $evento->data_fim,
                        'quantidade' => 1,
                        'status' => 'reservado',
                    ]);
                }
            }
        }
    }

    private function ativarItensReservados(EventosModel $evento)
    {
        $reservas = ReservaEquipamentosModel::where('evento_id', $evento->id)->get();

        foreach ($reservas as $reserva) {
            $item = ItensInventarioModel::find($reserva->item_id);

            if (!$item || $item->tipo !== 'equipamento') {
                continue;
            }

            // aqui você pode ainda garantir que não há OUTRO evento em_andamento
            // no mesmo horário, se quiser, mas SEM ver essa própria reserva

            // coloca em uso
            $item->quantidade_em_uso += 1;
            $item->quantidade_disponivel -= 1;
            $item->status = 'em_uso';
            $item->save();

            $reserva->status = 'em_uso';
            $reserva->save();
        }
    }

    private function devolverItensDoKit(EventosModel $evento)
    {
        $kits = EventoKitModel::where('evento_id', $evento->id)->get();

        foreach ($kits as $kitEvento) {

            $kit = KitsModel::with('itens')->find($kitEvento->kit_id);

            foreach ($kit->itens as $item) {

                if ($item->tipo !== 'equipamento') {
                    continue;
                }

                for ($i = 0; $i < $kitEvento->quantidade; $i++) {

                    if ($item->quantidade_em_uso > 0) {
                        $item->quantidade_em_uso -= 1;
                        $item->quantidade_disponivel += 1;
                    }
                }

                if ($item->quantidade_em_uso == 0) {
                    $item->status = 'disponivel';
                }

                $item->save();
            }
        }
    }

    private function removerReservasDoEvento(int $eventoId)
    {
        ReservaEquipamentosModel::where('evento_id', $eventoId)->delete();
    }
}
