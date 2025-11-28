<?php

namespace App\Http\Controllers;

use App\Http\Requests\PessoasRequest;
use App\Models\ColaboradoresModel;
use App\Models\EmpresaModel;
use App\Models\PermissoesModel;
use App\Models\PessoasModel;
use Illuminate\Http\Request;

class PessoasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $colaboradorId = ColaboradoresModel::where('usuario_id', $user->id)->value('empresa_id');

        if (!$user || $user->master === true) {
            $message = 'Lista de pessoas do usuário master';
            $funcionarios = PessoasModel::where('usuario_id', $user->id)->get();

            return response()->json([
                'message' => $message,
                'data' => $funcionarios
            ], 200);
        }

        $message = 'Lista de pessoas da empresa';
        $funcionarios = PessoasModel::where('empresa_id', $colaboradorId)
            ->with('usuario', 'empresa')
            ->get();

        return response()->json([
            'message' => $message,
            'data' => $funcionarios
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PessoasRequest $request)
    {
        $user = auth()->user();

        if (!$user || $user->master !== true) {
            $colaborador = ColaboradoresModel::where('usuario_id', $user->id)->first();
            $permissoes = PermissoesModel::where('colaborador_id', $colaborador->id)->first();
            $empresa = EmpresaModel::where('id', $colaborador->empresa_id)->first();

            if (!$permissoes || !$permissoes->gerenciar_pessoas) {
                return response()->json([
                    'message' => 'Você não tem permissão para cadastrar pessoas.'
                ], 403);
            }

            $data = array_merge($request->validated(), [
                'usuario_id' => $empresa->responsavel_id,
                'empresa_id' => $colaborador->empresa_id,
            ]);

            $funcionario = PessoasModel::create($data);

            return response()->json([
                'message' => 'Pessoa criada com sucesso',
                'data' => $funcionario
            ], 201);
        }

        $empresaId = EmpresaModel::where('responsavel_id', $user->id)->value('id');
        $data = array_merge($request->validated(), ['usuario_id' => $user->id,
            'empresa_id' => $empresaId,]);

        $funcionario = PessoasModel::create($data);
        return response()->json(['message' => 'Pessoa criada com sucesso',
            'data' => $funcionario], 201);
    }

    /**
     * Display the specified resource.
     */
    public
    function show(string $id)
    {
        $funcionario = PessoasModel::with('usuario', 'empresa')->findOrFail($id);
        return response()->json([
            'data' => $funcionario
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public
    function update(PessoasRequest $request, string $id)
    {
        $user = auth()->user();

        if (!$user || $user->master !== true) {
            $colaborador = ColaboradoresModel::where('usuario_id', $user->id)->first();
            $permissoes = PermissoesModel::where('colaborador_id', $colaborador->id)->first();

            if (!$permissoes || !$permissoes->gerenciar_pessoas) {
                return response()->json([
                    'message' => 'Você não tem permissão para atualizar pessoas.'
                ], 403);
            }
        }

        $funcionario = PessoasModel::findOrFail($id);
        $funcionario->update($request->validated());
        return response()->json([
            'message' => 'Pessoa atualizada com sucesso',
            'data' => $funcionario
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public
    function destroy(string $id)
    {
        $user = auth()->user();

        if (!$user || $user->master !== true) {
            $colaborador = ColaboradoresModel::where('usuario_id', $user->id)->first();
            $permissoes = PermissoesModel::where('colaborador_id', $colaborador->id)->first();

            if (!$permissoes || !$permissoes->gerenciar_pessoas) {
                return response()->json([
                    'message' => 'Você não tem permissão para deletar pessoas.'
                ], 403);
            }
        }

        $funcionario = PessoasModel::findOrFail($id);
        $funcionario->ativo = false;
        $funcionario->save();

        return response()->json([
            'message' => 'Pessoa desativada com sucesso'
        ], 201);
    }
}
