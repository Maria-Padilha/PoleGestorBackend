<?php

namespace App\Http\Controllers\evento;

use App\Http\Controllers\Controller;
use App\Models\evento\EventoFuncionarioModel;
use App\Models\evento\EventosModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventoFuncionarioController extends Controller
{
    /**
     * Adiciona um funcionário a um evento existente.
     * @param Request $request
     * @param $eventoId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function addFuncionario(Request $request, $eventoId)
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
            'funcionario_id' => 'required|exists:pessoas,id',
            'funcao'         => 'nullable|string|max:100',
            'horas_trabalhadas' => 'nullable|numeric|min:0',
            'custo'          => 'nullable|numeric|min:0',
            'observacoes'   => 'nullable|string',
        ]);

        $existe = EventoFuncionarioModel::where('evento_id', $eventoId)
            ->where('funcionario_id', $validated['funcionario_id'])
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'funcionario_id' => 'Este funcionário já está vinculado a este evento.'
            ]);
        }

        $funcionario = EventoFuncionarioModel::create([
            'evento_id'        => $eventoId,
            'funcionario_id'   => $validated['funcionario_id'],
            'funcao'           => $validated['funcao'] ?? null,
            'horas_trabalhadas'=> $validated['horas_trabalhadas'] ?? 0,
            'custo'            => $validated['custo'] ?? 0,
            'observacoes'     => $validated['observacoes'] ?? null,
        ]);

        // Retorno
        return response()->json([
            'message' => 'Funcionario vinculado ao evento com sucesso.',
            'cliente' => $funcionario
        ], 201);
    }

    /**
     * ATUALIZAR UM FUNCIONÁRIO DE UM EVENTO EXISTENTE.
     * @param Request $request
     * @param $eventoId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function updateFuncionario(Request $request, $eventoId, $funcionarioId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Verifica se o funcionário existe
        $funcionario = EventoFuncionarioModel::find($funcionarioId);

        if (!$funcionario) {
            throw ValidationException::withMessages([
                'funcionario' => 'Funcionário não encontrado.'
            ]);
        }

        // Validação dos dados
        $validated = $request->validate([
            'funcao'         => 'nullable|string|max:100',
            'horas_trabalhadas' => 'nullable|numeric|min:0',
            'custo'          => 'nullable|numeric|min:0',
            'observacoes'   => 'nullable|string',
        ]);

        // Atualiza os dados do funcionário
        $funcionario->update($validated);

        // Retorno
        return response()->json([
            'message' => 'Funcionário do evento atualizado com sucesso.',
            'funcionario' => $funcionario
        ], 200);
    }

    /**
     * Remove um funcionário de um evento.
     * @param $eventoId
     * @param $funcionarioId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function removeFuncionario($eventoId, $funcionarioId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Verifica se o funcionário existe
        $funcionario = EventoFuncionarioModel::find($funcionarioId);

        if (!$funcionario) {
            throw ValidationException::withMessages([
                'funcionario' => 'Funcionário não encontrado.'
            ]);
        }

        // Remove o funcionário do evento
        $funcionario->delete();

        // Retorno
        return response()->json([
            'message' => 'Funcionário removido do evento com sucesso.'
        ], 200);
    }

    /**
     * LISTAR FUNCIONÁRIOS DE UM EVENTO EXISTENTE.
     * @param $eventoId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function listFuncionarios($eventoId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Recupera os funcionários do evento
        $funcionarios = EventoFuncionarioModel::white('funcionario')->where('evento_id', $eventoId)->get();

        // Retorno
        return response()->json([
            'message' => 'Funcionários recuperados com sucesso.',
            'data' => $funcionarios
        ], 200);
    }

    /**
     * LISTAR FUNCIONÁRIOS DE UM EVENTO EXISTENTE POR ID.
     * @param $eventoId
     * @param $funcionarioId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function getFuncionario($eventoId, $funcionarioId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Recupera o funcionário do evento
        $funcionario = EventoFuncionarioModel::white('funcionario')->where('evento_id', $eventoId)
            ->where('id', $funcionarioId)
            ->first();

        if (!$funcionario) {
            throw ValidationException::withMessages([
                'funcionario' => 'Funcionário não encontrado para este evento.'
            ]);
        }

        // Retorno
        return response()->json([
            'message' => 'Funcionário recuperado com sucesso.',
            'data' => $funcionario
        ], 200);
    }
}
