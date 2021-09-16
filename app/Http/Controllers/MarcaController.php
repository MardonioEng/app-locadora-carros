<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;

class MarcaController extends Controller
{
    public function __construct(Marca $marca)
    {
        $this->marca = $marca;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        //$marcas = Marca::all();
        $marcas = $this->marca->all();
        return response()->json($marcas, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $marca = $this->marca->create($request->all());
        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        $marca = $this->marca->find($id);

        if($marca === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        return response()->json($marca, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): JsonResponse
    {
        $marca = $this->marca->find($id);

        if($marca === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        $marca->update($request->all());
        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response|string[]
     */
    public function destroy($id): JsonResponse
    {
        $marca = $this->marca->find($id);

        if($marca === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        $marca->delete();
        return response()->json(["msg" => "A marca foi removida com sucesso!"], 200);
    }
}
