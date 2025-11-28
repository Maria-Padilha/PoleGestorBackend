<?php

namespace App\Http\Controllers\evento;

use App\Http\Controllers\Controller;
use App\Models\evento\EventoClienteModel;
use App\Models\evento\EventosModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventoClienteController extends Controller
{
    /**
     * Adiciona um cliente a um evento existente.
     * @param Request $request
     * @param $eventoId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function addCliente(Request $request, $eventoId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Validação dos dados
        $validated = $request->validate([
            'cliente_id' => 'required|exists:pessoas,id',
        ]);

        // Criação do cliente
        $cliente = EventoClienteModel::create([
            'evento_id'    => $eventoId,
            'cliente_id' => $validated['cliente_id'],
        ]);

        // Retorno
        return response()->json([
            'message' => 'Cliente vinculado ao evento com sucesso.',
            'cliente' => $cliente
        ], 201);
    }

    /**
     * ATUALIZAR UM CLIENTE DE UM EVENTO EXISTENTE.
     * @param Request $request
     * @param $eventoId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function updateCliente(Request $request, $eventoId, $clienteId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Verifica se o cliente existe
        $cliente = EventoClienteModel::find($clienteId);

        if (!$cliente) {
            throw ValidationException::withMessages([
                'cliente' => 'Cliente não encontrado.'
            ]);
        }

        // Validação dos dados
        $validated = $request->validate([
            'cliente_id' => 'required|exists:pessoas,id',
        ]);

        // Atualização do cliente
        $cliente->update([
            'cliente_id' => $validated['cliente_id'],
        ]);

        // Retorno
        return response()->json([
            'message' => 'Cliente atualizado com sucesso.',
            'cliente' => $cliente
        ], 200);
    }

    /**
     * LISTAR CLIENTES DE UM EVENTO EXISTENTE.
     * @param $eventoId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function listClientes($eventoId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Recupera os clientes vinculados ao evento
        $clientes = EventoClienteModel::white('cliente')->where('evento_id', $eventoId)->get();

        // Retorno
        return response()->json([
            'message' => 'Clientes recuperados com sucesso.',
            'clientes' => $clientes
        ], 200);
    }

    /**
     * LISTAR CLIENTES DE UM EVENTO EXISTENTE POR ID.
     * @param $eventoId
     * @param $clienteId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function getCliente($eventoId, $clienteId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Recupera o cliente vinculado ao evento
        $cliente = EventoClienteModel::white('cliente')->where('evento_id', $eventoId)
            ->where('id', $clienteId)
            ->first();

        if (!$cliente) {
            throw ValidationException::withMessages([
                'cliente' => 'Cliente não encontrado para este evento.'
            ]);
        }

        // Retorno
        return response()->json([
            'message' => 'Cliente recuperado com sucesso.',
            'cliente' => $cliente
        ], 200);
    }

    /**
     * DELETE UM CLIENTE DE UM EVENTO EXISTENTE.
     * @param $eventoId
     * @param $clienteId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function deleteCliente($eventoId, $clienteId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Recupera o cliente vinculado ao evento
        $cliente = EventoClienteModel::where('evento_id', $eventoId)
            ->where('id', $clienteId)
            ->first();

        if (!$cliente) {
            throw ValidationException::withMessages([
                'cliente' => 'Cliente não encontrado para este evento.'
            ]);
        }

        // Deleta o cliente
        $cliente->delete();

        // Retorno
        return response()->json([
            'message' => 'Cliente removido do evento com sucesso.'
        ], 200);
    }
}
