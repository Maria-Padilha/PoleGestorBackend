<?php

namespace App\Http\Controllers;

use App\Models\PreRegistrosModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ValidacaoController extends Controller
{
    public function validarCep($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cep) !== 8) {
            return response()->json(['erro' => 'CEP inválido'], 422);
        }

        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->failed() || isset($response['erro'])) {
            return response()->json(['erro' => 'CEP não encontrado'], 404);
        }

        return response()->json($response->json());
    }

    public function validarCpfCnpj($documento)
    {
        $documento = preg_replace('/[^0-9]/', '', $documento);

        if (strlen($documento) === 11) {
            $valido = $this->validarCPF($documento);
            return response()->json([
                'tipo' => 'CPF',
                'documento' => $documento,
                'valido' => $valido
            ]);
        }

        if (strlen($documento) === 14) {
            $valido = $this->validarCNPJ($documento);
            return response()->json([
                'tipo' => 'CNPJ',
                'documento' => $documento,
                'valido' => $valido
            ]);
        }

        return response()->json([
            'erro' => 'Documento deve ter 11 dígitos (CPF) ou 14 dígitos (CNPJ).'
        ], 422);
    }

    private function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$t] != $d) {
                return false;
            }
        }

        return true;
    }

    private function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $multiplicadores1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $multiplicadores2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for ($i = 0; $i < 12; $i++) {
            $soma1 = ($soma1 ?? 0) + $cnpj[$i] * $multiplicadores1[$i];
        }

        $resto1 = $soma1 % 11;
        $digito1 = ($resto1 < 2) ? 0 : 11 - $resto1;

        if ($cnpj[12] != $digito1) {
            return false;
        }

        for ($i = 0; $i < 13; $i++) {
            $soma2 = ($soma2 ?? 0) + $cnpj[$i] * $multiplicadores2[$i];
        }

        $resto2 = $soma2 % 11;
        $digito2 = ($resto2 < 2) ? 0 : 11 - $resto2;

        return $cnpj[13] == $digito2;
    }

    /**
     * VALIDAR EMAIL
     * @param string $email
     */

    public function iniciarValidacao(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:pre_registros,email',
            'cpf_cnpj' => 'nullable|string|max:20|unique:pre_registros,cpf_cnpj',
            'telefone' => 'nullable|string',
        ]);

        // Gerar token único
        $token = Str::random(60);

        if ($request->has('cpf_cnpj')) {
            $response = $this->validarCpfCnpj($request->cpf_cnpj);
            if ($response) {
                $validacaoData = $response->getData();

                if ($validacaoData->erro ?? false) {
                    return response()->json(['error' => $validacaoData->erro], 422);
                }

                if (!$validacaoData->valido) {
                    return response()->json(['error' => 'CPF/CNPJ inválido.'], 422);
                }
            }
        }

        // Criar pré-registro
        $registro = PreRegistrosModel::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'cpf_cnpj' => $request->cpf_cnpj,
            'telefone' => $request->telefone,
            'token' => $token,
            'token_expires_at' => now()->addHours(2), // token expira em 2h
        ]);

        // Enviar email
        $this->enviarEmailValidacao($registro);

        return response()->json([
            'message' => 'Email enviado com sucesso.',
            'token' => $token
        ]);
    }

    private function enviarEmailValidacao($registro)
    {
        $url = config('app.frontend_url') . "/pricing?token=" . $registro->token;

        Mail::send('emails.validar-email', [
            'nome' => $registro->nome,
            'url'  => $url
        ], function ($message) use ($registro) {
            $message->to($registro->email)
                ->subject('Complete seu cadastro');
        });
    }

    public function validarToken($token)
    {
        $registro = PreRegistrosModel::where('token', $token)->first();

        if (!$registro) {
            return response()->json(['error' => 'Token inválido'], 404);
        }

        if ($registro->token_expires_at < now()) {
            return response()->json(['error' => 'Token expirado'], 401);
        }

        return response()->json([
            'nome' => $registro->nome,
            'email' => $registro->email,
            'cpf_cnpj' => $registro->cpf_cnpj,
            'telefone' => $registro->telefone,
        ]);
    }
}
