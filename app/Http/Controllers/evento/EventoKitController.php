<?php

namespace App\Http\Controllers\evento;

use App\Http\Controllers\Controller;
use App\Models\evento\EventoKitModel;
use App\Models\evento\EventosModel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class EventoKitController extends Controller
{
    /**
     * Adiciona um kit a um evento existente.
     * @param Request $request
     * @param $eventoId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function addKit(Request $request, $eventoId)
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
            'kit_id'    => 'required|exists:kits,id',
            'quantidade'=> 'required|numeric|min:1',
        ]);

        $existe = EventoKitModel::where('evento_id', $eventoId)
            ->where('kit_id', $validated['kit_id'])
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'kit_id' => 'Este kit já está vinculado a este evento.'
            ]);
        }

        $kit = EventoKitModel::create([
            'evento_id' => $eventoId,
            'kit_id'    => $validated['kit_id'],
            'quantidade'=> $validated['quantidade'],
        ]);

        // Retorno
        return response()->json([
            'message' => 'Kit adicionado ao evento com sucesso.',
            'data'    => $kit
        ], 201);
    }

    /**
     * Remove um kit vinculado a um evento.
     * @param $eventoId
     * @param $kitId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function removeKit($eventoId, $kitId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Verifica se o funcionário existe
        $kit = EventoKitModel::find($kitId);

        if (!$kit) {
            throw ValidationException::withMessages([
                'kit' => 'Kit não encontrado.'
            ]);
        }

        // Remove o kit
        $kit->delete();

        // Retorno
        return response()->json([
            'message' => 'Kit removido do evento com sucesso.'
        ], 200);
    }

    /**
     * Lista os kits vinculados a um evento.
     * @param $eventoId
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */

    public function listKits($eventoId)
    {
        // Verifica se o evento existe
        $evento = EventosModel::find($eventoId);

        if (!$evento) {
            throw ValidationException::withMessages([
                'evento' => 'Evento não encontrado.'
            ]);
        }

        // Recupera os kits do evento
        $kits = EventoKitModel::with('kit')->where('evento_id', $eventoId)->get();

        // Retorno
        return response()->json([
            'message' => 'Kits recuperados com sucesso.',
            'data'    => $kits
        ], 200);
    }
}
