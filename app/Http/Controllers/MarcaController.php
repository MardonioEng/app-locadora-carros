<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Repositories\MarcaRepository;
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
        $marcaRepository = new MarcaRepository($this->marca);

        if($request->has('atributos_modelos')) {
            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        }else {
            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');
        }

        if($request->has('filtro')) {
            $marcaRepository->filtro($request->filtro);
        }

        if($request->has('atributos')) {
            $marcaRepository->selectAtributos($request->atributos);
        }
        return response()->json($marcaRepository->getResultado(), 200);
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
            return response()->json(['erro' => 'recurso n??o encontrado'], 404);

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
            return response()->json(['erro' => 'recurso n??o encontrado'], 404);

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
        $marca->save(); //O m??todo save faz um update se o ID existir carso contr??rio, cria um novo registro

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
            return response()->json(['erro' => 'recurso n??o encontrado'], 404);

        //Remove um arquivo antigo caso um novo arquivo tenha sido enviado no request
        Storage::disk('public')->delete($marca->imagem);


        $marca->delete();
        return response()->json(["msg" => "A marca foi removida com sucesso!"], 200);
    }
}
