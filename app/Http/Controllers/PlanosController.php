<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanosModel;

class PlanosController extends Controller
{
    public function buscarTodos()
    {
        $planos = PlanosModel::all();
        return response()->json($planos);
    }

    public function buscarPeloId($id)
    {
        $plano = PlanosModel::find($id);
        if ($plano) {
            return response()->json($plano);
        } else {
            return response()->json(['message' => 'Plano não encontrado'], 404);
        }
    }
}
