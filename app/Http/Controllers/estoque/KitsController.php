<?php

namespace App\Http\Controllers\estoque;

use App\Http\Controllers\BaseController;
use App\Http\Requests\KitsRequest;
use App\Models\estoque\KitsModel;

class KitsController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kit = null;
        $message = '';

        if ($this->userMaster->master !== true) {
            $message = 'Lista de kits da empresa';
            $kit = KitsModel::where('empresa_id', $this->empresa->id)->with('itens')->get();
        }
        else {
            $message = 'Lista de kits do usuário master';
            $kit = KitsModel::where('usuario_id', $this->userMaster->id)->with('itens')->get();
        }

        // percorre cada kit
        $kit->transform(function ($kit) {

            // percorre cada item do kit
            $kit->itens->transform(function ($item) {

                // remove campos do pivot que você não quer mostrar
                unset($item->pivot->created_at);
                unset($item->pivot->updated_at);

                return $item;
            });

            return $kit;
        });

        return response()->json([
            'message' => $message,
            'data' => $kit
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KitsRequest $request)
    {
        $data = null;
        $kit = null;

        if ($this->userMaster->master !== true) {
            if (!$this->permissoes || !$this->permissoes->gerenciar_inventario) {
                return response()->json([
                    'message' => 'Você não tem permissão para cadastrar kits.'
                ], 403);
            }

            $data = array_merge($request->validated(), [
                'usuario_id' => $this->empresa->responsavel_id,
                'empresa_id' => $this->colaborador->empresa_id,
            ]);

            $kit = KitsModel::create($data);
        }

        else {
            $data = array_merge($request->validated(), [
                'usuario_id' => $this->userMaster->id,
                'empresa_id' => $this->empresa->id,
            ]);

            $kit = KitsModel::create($data);
        }

        if ($request->has('itens')) {

            $itens = [];

            foreach ($request->itens as $item) {
                $itens[$item['item_id']] = [
                    'quantidade' => $item['quantidade']
                ];
            }

            $kit->itens()->attach($itens);
        }

        return response()->json([
            'message' => 'Kit criado com sucesso',
            'data' => $kit->load('itens')
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kit = KitsModel::with('itens')->findOrFail($id);

        $kit->itens->transform(function ($item) {
            unset($item->pivot->created_at);
            unset($item->pivot->updated_at);
            return $item;
        });

        return response()->json([
            'message' => 'Kit retornado com sucesso',
            'data' => $kit
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KitsRequest $request, string $id)
    {

        if ($this->userMaster->master !== true) {
            if (!$this->permissoes || !$this->permissoes->gerenciar_inventario) {
                return response()->json([
                    'message' => 'Você não tem permissão para atualizar itens de inventário.'
                ], 403);
            }
        }

        $kit = KitsModel::findOrFail($id);

        $kit->update($request->only('nome', 'descricao', 'ativo'));

        if ($request->has('itens')) {
            $syncData = [];

            foreach ($request->itens as $item) {
                $syncData[$item['item_id']] = ['quantidade' => $item['quantidade']];
            }

            $kit->itens()->sync($syncData);
        }

        $kit->update($request->validated());
        return response()->json([
            'message' => 'Kit atualizado com sucesso',
            'data' => $kit
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kit = KitsModel::findOrFail($id);
        $kit->ativo = false;
        $kit->save();
        return response()->json([
            'message' => 'Kit desativado com sucesso'
        ], 201);
    }
}
