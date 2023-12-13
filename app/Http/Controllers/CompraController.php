<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function index(){
        # code
    }

    public function store(Request $request)
    {
        try {
            $productos = $request->input('productos');
    
            // Validar los productos
            if(empty($productos)){
                return ApiResponse::error('No se proporcionaron productos', 400);
            }
    
            // Validar la lista de productos
            $validator = Validator::make($request->all(), [
                'productos' => 'required|array',
                'productos.*.producto_id' => 'required|integer|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1'
            ]);
    
            if ($validator->fails()) {
                return ApiResponse::error('Datos inválidos en la lista de productos', 400, $validator->errors());
            }
    
            // Validar productos duplicados
            $productIds = array_column($productos, 'producto_id');
            if (count($productIds) !== count(array_unique($productIds))) {
                return ApiResponse::error('No se permiten productos duplicados para la compra', 400);
            }

    
        } catch (Exception $e) {
            // Aquí iría el manejo de la excepción
        }
    }
    

    public function show($id)
    {
        # code
    }
}
