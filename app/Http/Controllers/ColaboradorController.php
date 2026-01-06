<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColaboradorRequest;
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

        if (!$user || $user->master !== 1) {
            return response()->json([
                'message' => 'Apenas usuários master podem listar os colaboradores.'
            ], 403);
        }

        // Limites por plano
        $limites = [
            1 => 3,
            2 => 6,
            3 => 9,
        ];

        $limite = $limites[$user->plano_id] ?? null;

        // Lista colaboradores
        $colaboradores = ColaboradoresModel::whereHas('empresa', function ($query) use ($user) {
            $query->where('responsavel_id', $user->id);
        })->with(['permissoes', 'usuario'])->get();

        $quantidade = $colaboradores->count();

        // Pode cadastrar?
        $permitidoCadastrar = $limite === null || $quantidade < $limite;

        return response()->json([
            'permitido_cadastrar' => $permitidoCadastrar,
            'limite_plano' => $limite,
            'total_colaboradores' => $quantidade,
            'colaboradores' => $colaboradores,
        ]);
    }

    public function store(UserRequest $request, PermissoesRequest $permissoesRequest, ColaboradorRequest $colabRequest)
    {
        $user = auth()->user();

        if (!$user || $user->master !== 1) {
            return response()->json([
                'message' => 'Apenas usuários master podem cadastrar colaboradores.'
            ], 403);
        }

        // 1) Checa limite do plano
        $limites = [
            1 => 3,
            2 => 6,
            3 => 9,
        ];

        $limite = $limites[$user->plano_id] ?? null;

        $qtd = ColaboradoresModel::whereHas('empresa', function ($query) use ($user) {
            $query->where('responsavel_id', $user->id);
        })->count();

        if ($limite !== null && $qtd >= $limite) {
            return response()->json([
                'message' => "Seu plano atual permite apenas {$limite} colaboradores. Atualize seu plano para adicionar mais colaboradores.",
                'permitido_cadastrar' => false,
            ], 403);
        }

        // 2) Empresa do master
        $empresa = EmpresaModel::where('responsavel_id', $user->id)->first();

        if (!$empresa) {
            return response()->json([
                'message' => 'Você precisa cadastrar uma empresa antes de adicionar colaboradores.'
            ], 400);
        }

        // 3) Dados validados
        $userData = $request->validated();
        $colabData = $colabRequest->validated();
        $permData  = $permissoesRequest->validated();

        // 4) Monta payload do usuário
        $userData['plano_id'] = $user->plano_id;
        $userData['empresa_id'] = $empresa->id;
        $userData['tipo_usuario'] = 'colaborador';
        $userData['master'] = false;


        // 5) Cria tudo com transação
        $result = \DB::transaction(function () use ($userData, $colabData, $permData, $empresa) {

            $userData['empresa_id'] = $empresa->id;
            $newUser = User::create($userData);

            $colaborador = ColaboradoresModel::create([
                'usuario_id' => $newUser->id,
                'empresa_id' => $empresa->id,
                'funcao' => $colabData['funcao'] ?? null,
                'ativo' => $colabData['ativo'] ?? true,
            ]);

            $permData['colaborador_id'] = $colaborador->id;
            PermissoesModel::create($permData);

            return $colaborador;
        });

        return response()->json([
            'message' => 'Colaborador criado com sucesso',
            'permitido_cadastrar' => true,
            'colaborador' => $result->load(['usuario', 'permissoes']),
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
                'message' => 'Apenas usuários master podem atualizar colaboradores.'
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
                'message' => 'Você não tem permissão para atualizar colaboradores desta empresa.'
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

        if (!$user || $user->master !== 1) {
            return response()->json([
                'message' => 'Apenas usuários master podem remover os colaboradores.'
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
