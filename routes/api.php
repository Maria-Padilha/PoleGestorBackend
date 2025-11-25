<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidacaoController;

Route::get('/planos', [PlanosController::class, 'buscarTodos']);
Route::get('/planos/{id}', [PlanosController::class, 'buscarPeloId']);

Route::post('/login', [AuthController::class, 'login']);

Route::resource('usuarios', UserController::class);

Route::get('cep/{cep}', [ValidacaoController::class, 'validarCep']);
Route::get('documento/{documento}', [ValidacaoController::class, 'validarCpfCnpj']);

Route::post('/validar-email', [ValidacaoController::class, 'iniciarValidacao']);
Route::get('/validar-token/{token}', [ValidacaoController::class, 'validarToken']);

Route::middleware('auth:sanctum')->group(function () {

    // rotas de autenticação
    Route::get('/perfil', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
