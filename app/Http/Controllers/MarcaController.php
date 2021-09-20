<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $marcas = array();

        if($request->has('atributos_modelos')) {
            $atributos_modelos = $request->atributos_modelos;
            $marcas = $this->marca->with('modelos:id,'.$atributos_modelos);
        }else {
            $marcas = $this->marca->with('modelos');
        }

        if($request->has('filtro')) {
            $filtros = explode(';', $request->filtro);
            foreach ($filtros as $key => $condicao) {
                $c = explode(':', $condicao);
                $marcas = $marcas->where($c[0], $c[1], $c[2]);
            }
        }

        if($request->has('atributos')) {
            $atributos = $request->atributos;
            $marcas = $marcas->selectRaw($atributos)->get();
        }else {
            $marcas = $marcas->get();
        }

        //$marcas = $this->marca->with('modelos')->get();
        return response()->json($marcas, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate($this->marca->rules(), $this->marca->feedback());

        $imagem = $request->imagem;
        $imagem_urn = $imagem->store('imagens', 'public');

        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);

        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $marca = $this->marca->with('modelos')->find($id);

        if($marca === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        return response()->json($marca, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $marca = $this->marca->find($id);

        if($marca === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        if($request->method() === 'PATCH') {
            $regrasDinamicas = array();

            foreach ($marca->rules() as $input => $regra) {
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas, $marca->feedback());
        }else {
            $request->validate($marca->rules(), $marca->feedback());
        }

        //Remove um arquivo antigo caso um novo arquivo tenha sido enviado no request
        if($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
        }

        $imagem = $request->imagem;
        $imagem_urn = $imagem->store('imagens', 'public');

        //Preencher o objeto $marca com os dados do $request
        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;
        $marca->save(); //O método save faz um update se o ID existir carso contrário, cria um novo registro

        /*
        $marca->update([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);
        */

        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $marca = $this->marca->find($id);

        if($marca === null)
            return response()->json(['erro' => 'recurso não encontrado'], 404);

        //Remove um arquivo antigo caso um novo arquivo tenha sido enviado no request
        Storage::disk('public')->delete($marca->imagem);


        $marca->delete();
        return response()->json(["msg" => "A marca foi removida com sucesso!"], 200);
    }
}
