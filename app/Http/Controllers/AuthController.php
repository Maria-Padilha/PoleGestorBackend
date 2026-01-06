<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'senha' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();


        if (!$user || !Hash::check($request->senha, $user->senha)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'nome', 'email', 'cpf_cnpj'])
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->senha)) {
            return response()->json(['message' => 'Senha atual incorreta'], 400);
        }

        $user->senha = $request->new_password;
        $user->save();
        return response()->json(['message' => 'Senha atualizada com sucesso']);
    }

    public function me()
    {
        return response()->json(auth()->user()->load('planoAtual'));
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logout realizado']);
    }
}
