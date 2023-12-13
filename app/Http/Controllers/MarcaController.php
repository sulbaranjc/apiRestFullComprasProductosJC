<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Marca;
use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class MarcaController extends Controller
{
    public function index()
    {
        try {
            $marcas = Marca::all();
            return ApiResponse::success('Lista de Marcas', 200, $marcas);
        } catch (Exception $e) {
            return ApiResponse::error('Error al listar las Marcas: '.$e->getMessage() , 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:marcas|string|max:255'
            ]);

            $marca = Marca::create($request->all());
            return ApiResponse::success('Marca creada con exito', 201, $marca);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error al crear la Marca: '.$e->getMessage() , 422);
        }
    }

    public function show($id)
    {
        try {
            $marca = Marca::find($id);
            if($marca){
                return ApiResponse::success('Marca encontrada', 200, $marca);
            }else{
                return ApiResponse::error('Marca no encontrada', 404);
            }
        } catch (Exception $e) {
            return ApiResponse::error('Error al buscar la Marca: ', 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $request->validate([
                'nombre' => ['required', Rule::unique('marcas')->ignore($marca)]
            ]);
            $marca->update($request->all());
            return ApiResponse::success('Categoría actualizada exitosamente', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error('Error: '.$e->getMessage(), 422);
        }
    }
    

    public function destroy($id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $marca->delete();
            return ApiResponse::success('Categoría eliminada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error('Error no se procesó la petición', 422);
        }
    }

    public function productosPorMarca($id)
    {
        try {
            $marca = Marca::with('productos')->findOrFail($id);
            return ApiResponse::success('Marca y lista de productos', 200, $marca);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        }
            # code
    }
}


