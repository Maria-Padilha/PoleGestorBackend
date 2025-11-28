<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmpresaRequest;
use App\Models\ColaboradoresModel;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EmpresaModel;

class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user || $user->master !== true) {
            $colab = ColaboradoresModel::where('usuario_id', $user->id);
            return response()->json([
                'message' => 'Lista de empresas vinculadas ao colaborador.',
                'data' => $colab->with('empresa.responsavel')->get()->map(function ($item) {
                    return $item->empresa;
                })
            ], 200);
        }

        $empresas = EmpresaModel::where('responsavel_id', $user->id)->get();

        return response()->json([
            'message' => 'Lista de empresas do usuário master.',
            'data' => $empresas
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmpresaRequest $request)
    {
        $user = auth()->user();

        if (!$user || $user->master !== true) {
            return response()->json([
                'error' => 'Apenas usuários master podem cadastrar uma empresa.'
            ], 403);
        }

        $empresa = EmpresaModel::create([
            'responsavel_id' => $user->id,
            ...$request->validated(),
        ]);

        $user->empresa_id = $empresa->id;
        $user->save();

        return response()->json([
            'message' => 'Empresa cadastrada com sucesso!',
            'data' => $empresa
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmpresaRequest $request, string $id)
    {
        $user = auth()->user();

        // 🔒 Apenas master pode atualizar empresa
        if (!$user || $user->master !== true) {
            return response()->json([
                'error' => 'Apenas usuários master podem atualizar empresas.'
            ], 403);
        }

        // Buscar empresa
        $empresa = EmpresaModel::find($id);
        if (!$empresa) {
            return response()->json([
                'message' => 'Empresa não encontrada.'
            ], 404);
        }

        // Validação (ideal: criar EmpresaUpdateRequest separado)
        $validated = $request->validated();

        // Atualizar empresa
        $empresa->update($validated);

        return response()->json([
            'message' => 'Empresa atualizada com sucesso.',
            'data' => $empresa
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();

        $empresa = EmpresaModel::find($id);
        if (!$empresa) {
            return response()->json([
                'message' => 'Empresa não encontrada.'
            ], 404);
        }

        if (!$user || $user->master !== true || $empresa->responsavel_id !== $user->id) {
            return response()->json([
                'error' => 'Apenas o usuário master responsável pode deletar esta empresa.'
            ], 403);
        }

        if (ColaboradoresModel::where('empresa_id', $empresa->id)->exists()) {
            return response()->json([
                'error' => 'Não é possível deletar a empresa pois existem colaboradores vinculados a ela.'
            ], 409);
        }

        $empresa->delete();
        return response()->json([
            'message' => 'Empresa deletada com sucesso.'
        ], 200);
    }
}
