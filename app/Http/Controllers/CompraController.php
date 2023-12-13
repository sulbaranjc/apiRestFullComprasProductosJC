<?php



namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Compra;
use App\Models\Producto;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



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

            $totalPagar = 0;
            $subtotal = 0;
            $compraItems = [];

            // Iteración de los productos para calcular el total a pagar
            foreach ($productos as $producto) {
                $productoB = Producto::find($producto['producto_id']);
                if (!$productoB) {
                    return ApiResponse::error('Producto no encontrado', 404);
                }

                // Validar la cantidad disponible de los productos
                if ($productoB->cantidad_disponible < $producto['cantidad']) {
                    return ApiResponse::error('El producto no tiene suficiente cantidad disponible', 404);
                }

                // Actualización de la cantidad disponible de cada producto
                $productoB->cantidad_disponible -= $producto['cantidad'];
                $productoB->save();

                // Cálculo de los importes
                $subtotal = $productoB->precio * $producto['cantidad'];
                $totalPagar += $subtotal;
               
                // Items de la compra
                $compraItems[] = [
                    'producto_id' => $productoB->id,
                    'precio' => $productoB->precio,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $subtotal
                ];
            }
            // Registro en la tabla compra
            $compra = Compra::create([
                'subtotal' => $totalPagar,
                'total' => $totalPagar
            ]);
            // Asociar los productos a la compra con sus cantidades y sus subtotales
            $compra->productos()->attach($compraItems);

            return ApiResponse::success('Compra realizada exitosamente', 201, $compra);
   
        } catch (QueryException $e) {
            // error de consulta en la base de datos
            return ApiResponse::error('Error en la consulta de base de datos', 500);
        } catch (Exception $e) {
            return ApiResponse::error('Error inesperado', 500);
        }
        
    }
    

    public function show($id)
    {
        # code
    }
}
