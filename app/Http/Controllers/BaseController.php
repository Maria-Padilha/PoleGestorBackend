<?php

namespace App\Http\Controllers;

use App\Models\ColaboradoresModel;
use App\Models\EmpresaModel;
use App\Models\PermissoesModel;
use App\Services\EstoqueService;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected $userMaster;
    protected $empresa;
    protected $colaborador;
    protected $permissoes;

    protected EstoqueService $service;

    public function __construct(EstoqueService $service, $message = null)
    {
        $this->userMaster = auth()->user();
        $this->empresa = EmpresaModel::where('responsavel_id', $this->userMaster->id)->first();

        if ($this->userMaster->master !== true) {
            if ($message) {
                return response()->json([
                    'message' => $message
                ], 403);
            }

            $this->colaborador = ColaboradoresModel::where('usuario_id', $this->userMaster->id)->first();
            $this->empresa = EmpresaModel::where('id', $this->colaborador->empresa_id)->first();
            $this->permissoes = PermissoesModel::where('colaborador_id', $this->colaborador->id)->first();
        }

        $this->service = $service;
    }
}
