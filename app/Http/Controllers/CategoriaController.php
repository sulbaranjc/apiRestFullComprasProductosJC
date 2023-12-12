<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class CategoriaController extends Controller
{
    public function index()
    {
        try {
            $categorias = Categoria::all();
            return ApiResponse::success('Lista de Categorias', 200, $categorias);
        } catch (Exception $e) {
            return ApiResponse::error('Error al listar las categorias: '.$e->getMessage() , 500);
        }
    }
     
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:categorias|string|max:255'
            ]);

            $categoria = Categoria::create($request->all());
            return ApiResponse::success('Categoria creada con exito', 201, $categoria);
        } catch (ValidationException $e) {
            return ApiResponse::error('Error al crear la categoria: '.$e->getMessage() , 422);
        }
    }

    public function show($id)
    {
        try {
            $categoria = Categoria::find($id);
            if($categoria){
                return ApiResponse::success('Categoria encontrada', 200, $categoria);
            }else{
                return ApiResponse::error('Categoria no encontrada', 404);
            }
        } catch (Exception $e) {
            return ApiResponse::error('Error al buscar la categoria: ', 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $request->validate([
                'nombre' => ['required', Rule::unique('categorias')->ignore($categoria)]
            ]);
            $categoria->update($request->all());
            return ApiResponse::success('Categoría actualizada exitosamente', 200, $categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error('Error: '.$e->getMessage(), 422);
        }
    }
    

    public function destroy($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();
            return ApiResponse::success('Categoría eliminada exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error('Error no se procesó la petición', 422);
        }
    }

    public function productosPorCategoria($id)
    {
        try {
            $categoria = Categoria::with('productos')->findOrFail($id);
            return ApiResponse::success('Categoría y lista de productos', 200, $categoria);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoría no encontrada', 404);
        }
    }
    
}
