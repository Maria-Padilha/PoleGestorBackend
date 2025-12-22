<?php

namespace App\Http\Controllers\estoque;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ItensInventarioRequest;
use App\Models\estoque\ItensInventarioModel;

class ItensInventarioController extends BaseController
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $itens = null;
        $message = '';

        if ($this->userMaster->master === true) {
            $message = 'Lista de itens de inventário do usuário master';
            $itens = ItensInventarioModel::where('usuario_id', $this->userMaster->id)->with('reservas')->get();

        } else {
            $message = 'Lista de itens de inventário da empresa';
            $itens = ItensInventarioModel::where('empresa_id', $this->empresa->id)
                ->with('usuario', 'empresa', 'reservas')
                ->get();
        }

        return response()->json([
            'message' => $message,
            'data' => $itens
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItensInventarioRequest $request)
    {
        if ($this->userMaster->master !== true) {
            if (!$this->permissoes || !$this->permissoes->gerenciar_inventario) {
                return response()->json([
                    'message' => 'Você não tem permissão para cadastrar itens de inventário.'
                ], 403);
            }

            $data = array_merge($request->validated(), [
                'usuario_id' => $this->empresa->responsavel_id,
                'empresa_id' => $this->colaborador->empresa_id,
            ]);

            $itens = ItensInventarioModel::create($data);

            return response()->json([
                'message' => 'Item de inventário criado com sucesso.',
                'data' => $itens
            ], 201);
        }

        else {
            $data = array_merge($request->validated(), [
                'usuario_id' => $this->userMaster->id,
                'empresa_id' => $this->empresa ? $this->empresa->id : null,
            ]);

            $itens = ItensInventarioModel::create($data);

            return response()->json([
                'message' => 'Item de inventário criado com sucesso.',
                'data' => $itens
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $itens = ItensInventarioModel::findOrFail($id);
        return response()->json([
            'message' => 'Item de inventário recuperado com sucesso.',
            'data' => $itens
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItensInventarioRequest $request, string $id)
    {
        if ($this->userMaster->master !== true) {
            if (!$this->permissoes || !$this->permissoes->gerenciar_inventario) {
                return response()->json([
                    'message' => 'Você não tem permissão para atualizar itens de inventário.'
                ], 403);
            }
        }

        $itens = ItensInventarioModel::findOrFail($id);
        $itens->update($request->validated());
        return response()->json([
            'message' => 'Item de inventário atualizado com sucesso.',
            'data' => $itens
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($this->userMaster->master !== true) {
            if (!$this->permissoes || !$this->permissoes->gerenciar_inventario) {
                return response()->json([
                    'message' => 'Você não tem permissão para remover itens de inventário.'
                ], 403);
            }
        }

        $itens = ItensInventarioModel::findOrFail($id);

        if (!$id) {
            return response()->json([
                'message' => 'Item de inventário não encontrado.'
            ], 404);
        }

        $itens->delete();
        return response()->json([
            'message' => 'Item de inventário deletado com sucesso.'
        ], 200);
    }
}
