<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\estoque\ItensInventarioController;
use App\Http\Controllers\estoque\KitsController;
use App\Http\Controllers\estoque\MovimentacaoEstoqueController;
use App\Http\Controllers\evento\EventoController;
use App\Http\Controllers\PermissoesController;
use App\Http\Controllers\PessoasController;
use App\Http\Controllers\PlanosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidacaoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\evento\EventoClienteController;
use App\Http\Controllers\evento\EventoFuncionarioController;
use App\Http\Controllers\evento\EventoKitController;
use App\Http\Controllers\ColaboradorController;

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

    Route::resource('pessoas', PessoasController::class);
    Route::resource('itens-inventario', ItensInventarioController::class);
    Route::resource('kits', KitsController::class);

    Route::resource('eventos', EventoController::class);

    Route::post('evento-cliente/{id}', [EventoClienteController::class, 'addCliente']);
    Route::put('evento-cliente/{idEvento}/{id}', [EventoClienteController::class, 'updateCliente']);
    Route::get('evento-cliente/{idEvento}', [EventoClienteController::class, 'listClientes']);
    Route::get('evento-cliente/{idEvento}/{id}', [EventoClienteController::class, 'getCliente']);
    Route::delete('evento-cliente/{idEvento}/{id}', [EventoClienteController::class, 'deleteCliente']);

    Route::post('evento-funcionario/{id}', [EventoFuncionarioController::class, 'addFuncionario']);
    Route::put('evento-funcionario/{idEvento}/{id}', [EventoFuncionarioController::class, 'updateFuncionario']);
    Route::get('evento-funcionario/{idEvento}', [EventoFuncionarioController::class, 'listFuncionarios']);
    Route::get('evento-funcionario/{idEvento}/{id}', [EventoFuncionarioController::class, 'getFuncionario']);
    Route::delete('evento-funcionario/{idEvento}/{id}', [EventoFuncionarioController::class, 'removeFuncionario']);

    Route::post('evento-kit/{id}', [EventoKitController::class, 'addKit']);
    Route::delete('evento-kit/{idEvento}/{id}', [EventoKitController::class, 'removeKit']);
    Route::get('evento-kit/{idEvento}', [EventoKitController::class, 'listKits']);

    Route::post('finalizar-evento/{id}', [EventoController::class, 'finalizarEvento']);

    Route::prefix('estoque')->group(function () {
        Route::post('/movimentar', [MovimentacaoEstoqueController::class, 'movimentar']);
        Route::get('/movimentacoes', [MovimentacaoEstoqueController::class, 'listar']);
    });

    Route::prefix('colab')->group(function () {
        Route::get('/', [ColaboradorController::class, 'index']);
        Route::post('/', [ColaboradorController::class, 'store']);
        Route::put('{colaboradorId}', [ColaboradorController::class, 'update']);
        Route::delete('{colaboradorId}', [ColaboradorController::class, 'destroy']);
    });

    // EMPRESAS
    Route::prefix('empresas')->group(function () {
        Route::get('/', [EmpresaController::class, 'index']);
        Route::post('/', [EmpresaController::class, 'store']);
        Route::put('/{id}', [EmpresaController::class, 'update']);
        Route::delete('/{id}', [EmpresaController::class, 'destroy']);
    });

    Route::resource('permissoes', PermissoesController::class);

});
