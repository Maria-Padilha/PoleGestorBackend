<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ValidacaoController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\error;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('colaboradores', 'empresasResponsavel')->get();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Email já cadastrado.'], 409);
        }

        if (User::where('cpf_cnpj', $request->cpf_cnpj)->exists()) {
            return response()->json(['error' => 'CPF/CNPJ já cadastrado.'], 409);
        }

        if ($request->cpf_cnpj) {
            $response = $this->validarCampo($request, 'cpf_cnpj');

            if ($response && $response->original['error']) {
                return response()->json(['error' => $response->original['error']], 422);
            }
        }

        $user = User::create($request->all());
        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $User = User::find($id);
        return response()->json($User);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $user = User::find($id);

        if ($request->cpf_cnpj) {
            $response = $this->validarCampo($request, 'cpf_cnpj');

            if ($response && $response->original['error']) {
                return response()->json(['error' => $response->original['error']], 422);
            }
        }

        $user->update($request->all());
        return response()->json($user, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::destroy($id);
        return response()->json('Usuário deletado com sucesso!', 200);
    }

    private function validarCampo($request, $campo)
    {
        if ($request->$campo) {
            $validacaoController = new ValidacaoController();
            $validacaoResponse = $validacaoController->validarCpfCnpj($request->$campo);
            $validacaoData = $validacaoResponse->getData();

            if ($validacaoData->erro ?? false) {
                return response()->json(['error' => $validacaoData->erro], 422);
            }

            if (!$validacaoData->valido) {
                return response()->json(['error' => 'CPF/CNPJ inválido.'], 422);
            }
        }
    }
}
