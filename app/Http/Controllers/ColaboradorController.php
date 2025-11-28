<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissoesRequest;
use App\Http\Requests\UserRequest;
use App\Models\ColaboradoresModel;
use App\Models\EmpresaModel;
use App\Models\PermissoesModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ColaboradorController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user || $user->master !== true) {
            return response()->json([
                'error' => 'Apenas usuários master podem listar os colaboradores.'
            ], 403);
        }

        $colaboradores = ColaboradoresModel::whereHas('empresa', function ($query) use ($user) {
            $query->where('responsavel_id', $user->id);
        })->with('permissoes')->get();

        return response()->json([
            'colaboradores' => $colaboradores
        ]);
    }

    public function store(UserRequest $request, PermissoesRequest $permissoesRequest)
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | 1. VERIFICAR SE É MASTER
        |--------------------------------------------------------------------------
        */
        if (!$user || $user->master !== true) {
            return response()->json([
                'error' => 'Apenas usuários master podem cadastrar colaboradores.'
            ], 403);
        }

        if ($user->plano_id === 1 && ColaboradoresModel::whereHas('empresa', function ($query) use ($user) {
            $query->where('responsavel_id', $user->id);
        })->count() >= 3) {
            return response()->json([
                'error' => 'Seu plano atual permite apenas 3 colaboradores. Atualize seu plano para adicionar mais colaboradores.'
            ], 403);
        }

        if ($user->plano_id === 2 && ColaboradoresModel::whereHas('empresa', function ($query) use ($user) {
            $query->where('responsavel_id', $user->id);
        })->count() >= 6) {
            return response()->json([
                'error' => 'Seu plano atual permite apenas 6 colaboradores. Atualize seu plano para adicionar mais colaboradores.'
            ], 403);
        }

        if ($user->plano_id === 3 && ColaboradoresModel::whereHas('empresa', function ($query) use ($user) {
            $query->where('responsavel_id', $user->id);
        })->count() >= 9) {
            return response()->json([
                'error' => 'Você atingiu o limite máximo de colaboradores para o seu plano.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. VERIFICAR SE O MASTER TEM EMPRESA VINCULADA
        |--------------------------------------------------------------------------
        */
        $empresa = EmpresaModel::where('responsavel_id', $user->id)->first();

        if (!$empresa) {
            return response()->json([
                'error' => 'Você precisa cadastrar uma empresa antes de adicionar colaboradores.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. VALIDAR DADOS DO COLABORADOR
        |--------------------------------------------------------------------------
        */
        $validated = $request;

        /*
        |--------------------------------------------------------------------------
        | 4. CRIAR USUÁRIO
        |--------------------------------------------------------------------------
        */
        $validated['plano_id'] = $user->plano_id;
        $validated['tipo_usuario'] = 'colaborador';
        $validated['master'] = false;

        $newUser = User::create($validated->all());

        /*
        |--------------------------------------------------------------------------
        | 5. CRIAR REGISTRO DO COLABORADOR
        |--------------------------------------------------------------------------
        */

        $colaborador = ColaboradoresModel::create([
            'usuario_id' => $newUser->id,
            'empresa_id' => $empresa->id,  // Aqui vincula automaticamente!
            'funcao' => $validated['funcao'] ?? null,
            'ativo' => true,
        ]);

        /*
       |--------------------------------------------------------------------------
       | 5. CRIAR PERMISSOES PARA O COLABORADOR
       |--------------------------------------------------------------------------
       */

        $permissoesRequestData = $permissoesRequest->validated();
        $permissoesRequestData['colaborador_id'] = $colaborador->id;
        PermissoesModel::create($permissoesRequestData);

        return response()->json([
            'message' => 'Colaborador criado com sucesso',
            'colaborador' => $colaborador->load('usuario'),
        ], 201);
    }

    public function update(UserRequest $request, $colaboradorId)
    {
        $user = auth()->user();
        $empresaId = EmpresaModel::where('responsavel_id', $user->id)->value('id');

        /*
        |--------------------------------------------------------------------------
        | 1. VERIFICAR SE O USUÁRIO É MASTER
        |--------------------------------------------------------------------------
        */
        if (!$user || $user->tipo_usuario !== 'master') {
            return response()->json([
                'error' => 'Apenas usuários master podem atualizar colaboradores.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. VERIFICAR SE A EMPRESA PERTENCE AO MASTER
        |--------------------------------------------------------------------------
        */
        $empresa = EmpresaModel::where('id', $empresaId)
            ->where('responsavel_id', $user->id)
            ->first();

        if (!$empresa) {
            return response()->json([
                'error' => 'Você não tem permissão para atualizar colaboradores desta empresa.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. VERIFICAR SE O COLABORADOR FAZ PARTE DA EMPRESA
        |--------------------------------------------------------------------------
        */
        $colaborador = ColaboradoresModel::with('usuario')
            ->where('empresa_id', $empresa->id)
            ->findOrFail($colaboradorId);

        /*
        |--------------------------------------------------------------------------
        | 4. VALIDAR DADOS
        |--------------------------------------------------------------------------
        */
        $validated = $request;

        /*
        |--------------------------------------------------------------------------
        | 5. ATUALIZAR USER
        |--------------------------------------------------------------------------


        $colaborador->usuario->update($validated->all());

        /*
        |--------------------------------------------------------------------------
        | 6. ATUALIZAR COLABORADOR
        |--------------------------------------------------------------------------
        */

        $validated = $request->toArray();

        if (array_key_exists('funcao', $validated)) {
            $colaborador->funcao = $validated['funcao'];
        }

        if (array_key_exists('ativo', $validated)) {
            $colaborador->ativo = $validated['ativo'];
        }

        $colaborador->save();

        return response()->json([
            'message' => 'Colaborador atualizado com sucesso!',
            'colaborador' => $colaborador->load('usuario'),
        ], 200);
    }

    public function destroy($colaboradorId)
    {
        $user = auth()->user();
        $empresaId = EmpresaModel::where('responsavel_id', $user->id)->value('id');

        if (!$user || $user->master !== true) {
            return response()->json([
                'error' => 'Apenas usuários master podem remover os colaboradores.'
            ], 403);
        }

        $colaborador = ColaboradoresModel::where('empresa_id', $empresaId)
            ->findOrFail($colaboradorId);

        // Apaga usuário também, se quiser
        User::where('id', $colaborador->usuario_id)->delete();
        ColaboradoresModel::destroy($colaboradorId);

        return response()->json([
            'message' => 'Colaborador removido com sucesso'
        ]);
    }
}
