<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissoesRequest;
use App\Models\ColaboradoresModel;
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

        // 1️⃣ Apenas master
        if (!$user || $user->master !== 1) {
            return response()->json([
                'message' => 'Apenas usuários master podem listar as permissões.'
            ], 403);
        }

        // 2️⃣ Empresa do master
        $empresa = EmpresaModel::where('responsavel_id', $user->id)->first();

        if (!$empresa) {
            return response()->json([
                'message' => 'Empresa do usuário master não encontrada.',
                'permissoes' => []
            ]);
        }

        // 3️⃣ Buscar permissões SOMENTE dos colaboradores da empresa do master
        $permissoes = PermissoesModel::whereHas('colaborador', function ($q) use ($empresa) {
            $q->where('empresa_id', $empresa->id);
        })
            ->with(['colaborador.usuario'])
            ->get();

        return response()->json([
            'message' => 'Lista de permissões da empresa',
            'empresa_id' => $empresa->id,
            'total' => $permissoes->count(),
            'permissoes' => $permissoes,
        ]);
    }

    public function permissoesDoColaborador(int $colaboradorId)
    {
        $user = auth()->user();

        // 1️⃣ Apenas master
        if (!$user || $user->master !== 1) {
            return response()->json([
                'error' => 'Apenas usuários master podem listar permissões.'
            ], 403);
        }

        // 2️⃣ Empresa do master
        $empresa = EmpresaModel::where('responsavel_id', $user->id)->first();

        if (!$empresa) {
            return response()->json([
                'error' => 'Empresa do usuário master não encontrada.'
            ], 404);
        }

        // 3️⃣ Busca o colaborador PELO ID (e garante que é da empresa do master)
        $colaborador = ColaboradoresModel::where('id', $colaboradorId)
            ->where('empresa_id', $empresa->id)
            ->with('usuario')
            ->first();

        if (!$colaborador) {
            return response()->json([
                'error' => 'Colaborador não encontrado ou não pertence à sua empresa.'
            ], 404);
        }

        // 4️⃣ Busca permissões pelo colaborador_id
        $permissoes = PermissoesModel::where('colaborador_id', $colaborador->id)
            ->first();

        if (!$permissoes) {
            return response()->json([
                'error' => 'Permissões não encontradas para este colaborador.'
            ], 404);
        }

        return response()->json([
            'message' => 'Permissões do colaborador',
            'empresa_id' => $empresa->id,
            'colaborador_id' => $colaborador->id,
            'usuario' => $colaborador->usuario,
            'permissoes' => $permissoes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissoesRequest $request, string $idColaborador)
    {
        $user = auth()->user();

        if (!$user || $user->master !== 1) {
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
