<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Producto;
use App\Models\Marca;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
        try {
            $productos = Producto::with([
                'marca' => function ($query) {
                    $query->select('id', 'nombre');
                },
                'categoria' => function ($query) {
                    $query->select('id', 'nombre');
                }
            ])->get();
            //$productos = Producto::with('marca', 'categoria')->get();
            return ApiResponse::success('Lista de Productos', 200, $productos);
        } catch (Exception $e) {
            return ApiResponse::error('Error al listar los Productos: '.$e->getMessage() , 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:productos',
                'precio' => 'required|numeric|between:0.99,999999.99',
                'cantidad_disponible' => 'required|integer|min:0|max:2147483647',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id',
            ]);
    
            $producto = Producto::create($request->all());
    
            return ApiResponse::success('Producto creado exitosamente', 201, $producto);

        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();

            if (isset($errors['nombre'])) {
                $errors['nombre del producto'] = 'El nombre del producto ya existe';
                unset($errors['nombre']);
            }

            if (isset($errors['categoria_id'])) {
                $errors['categoria'] = 'La categoria no existe';
                unset($errors['categoria_id']);
            }

            if (isset($errors['marca_id'])) {
                $errors['marca'] = 'La marca no existe';
                unset($errors['marca_id']);                
            }

            if (isset($errors['precio'])) {
                $errors['precio del producto'] = 'El precio debe ser un número entre 0.99 y 999999.99';
                unset($errors['precio']);
            }
            if (isset($errors['cantidad_disponible'])) {
                $errors['Existencia'] = 'La cantidad disponible debe ser un número entre 0 y 2147483647';
                unset($errors['cantidad_disponible']);
            }
            return ApiResponse::error('Errores de validación', 422, $errors);
        }
    }
    

    public function show($id)
    {
        try {
            $producto = Producto::with([
                'marca' => function ($query) {
                    $query->select('id', 'nombre');
                },
                'categoria' => function ($query) {
                    $query->select('id', 'nombre');
                }
            ])->findOrFail($id);

            //$producto = Producto::with('marca', 'categoria')->findOrFail($id);

            return ApiResponse::success('Producto obtenido exitosamente', 200, $producto);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 404);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $request->validate([
                'nombre' => 'required|unique:productos,nombre,'.$producto->id,
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id',
            ]);
            $producto->update($request->all());
            return ApiResponse::success('Producto actualizado exitosamente', 200, $producto);
        } catch (ValidationException  $e) {
            $errors = $e->validator->errors()->toArray();
    
            if (isset($errors['categoria_id'])) {
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }
    
            if (isset($errors['marca_id'])) {
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }
            
            return ApiResponse::error('Errores de validación', 422, $errors);
        }catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 404);
        }
    }
    
    

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->delete();
            return ApiResponse::success('Producto eliminado exitosamente', 200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado', 404);
        }
    }
    
}
