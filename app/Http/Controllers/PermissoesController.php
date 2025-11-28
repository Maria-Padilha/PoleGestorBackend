<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissoesRequest;
use App\Models\EmpresaModel;
use App\Models\PermissoesModel;
use Illuminate\Http\Request;

class PermissoesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user || $user->master !== true) {
            return response()->json([
                'error' => 'Apenas usuários master podem listar as permissões.'
            ], 403);
        }

        $perm = PermissoesModel::with('colaborador.usuario')->get();

        return response()->json([
            'message' => 'Lista de permissões',
            'permissoes' => $perm
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissoesRequest $request, string $idColaborador)
    {
        $user = auth()->user();

        if (!$user || $user->master !== true) {
            return response()->json([
                'error' => 'Apenas usuários master podem atualizar permissões.'
            ], 403);
        }

        $permissoes = PermissoesModel::where('colaborador_id', $idColaborador)->first();

        if (!$permissoes) {
            return response()->json([
                'error' => 'Permissões do colaborador não encontradas.'
            ], 404);
        }

        $permissoes->update($request->validated());

        return response()->json([
            'message' => 'Permissões atualizadas com sucesso.',
            'permissoes' => $permissoes
        ]);
    }
}
