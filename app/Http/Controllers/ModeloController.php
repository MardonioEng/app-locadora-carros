<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModeloController extends Controller
{

    public function __construct(Modelo $modelo)
    {
        $this->modelo = $modelo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $modelos = $this->modelo->with('marca')->get();
        return response()->json($modelos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate($this->modelo->rules());

        $imagem = $request->imagem;
        $imagem_urn = $imagem->store('imagens/modelos', 'public');

        $modelo = $this->modelo->create([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' => $request->numero_portas,
            'lugares' => $request->lugares,
            'air_bag' => $request->air_bag,
            'abs' => $request->abs
        ]);

        return response()->json($modelo, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $modelo = $this->modelo->with('marca')->find($id);

        if($modelo === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        return response()->json($modelo, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Modelo  $modelo
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $modelo = $this->modelo->find($id);

        if($modelo === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        if($request->method() === 'PATCH') {
            $regrasDinamicas = array();

            foreach ($modelo->rules() as $input => $regra) {
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas);
        }else {
            $request->validate($modelo->rules());
        }

        //Remove um arquivo antigo caso um novo arquivo tenha sido enviado no request
        if($request->file('imagem')) {
            Storage::disk('public')->delete($modelo->imagem);
        }

        $imagem = $request->imagem;
        $imagem_urn = $imagem->store('imagens/modelos', 'public');

        $modelo->fill($request->all());
        $modelo->imagem = $imagem_urn;
        $modelo->save();

        /*
        $modelo->update([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' => $request->numero_portas,
            'lugares' => $request->lugares,
            'air_bag' => $request->air_bag,
            'abs' => $request->abs
        ]);
        */

        return response()->json($modelo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $modelo = $this->modelo->find($id);

        if($modelo === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        //Remove um arquivo antigo caso um novo arquivo tenha sido enviado no request
        Storage::disk('public')->delete($modelo->imagem);


        $modelo->delete();
        return response()->json(["msg" => "A marca foi removida com sucesso!"]);
    }
}
