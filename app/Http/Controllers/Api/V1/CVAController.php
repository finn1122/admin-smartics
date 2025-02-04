<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Inventory\InventoryController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use App\Repositories\CVARepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CVAController extends Controller
{
    protected CVARepository $cvaRepository;
    protected InventoryController $inventoryController;
    protected ProductController $productController;

    public function __construct(CVARepository $cvaRepository, InventoryController $inventoryController, ProductController $productController)
    {
        $this->cvaRepository = $cvaRepository;
        $this->inventoryController = $inventoryController;
        $this->productController = $productController;
    }

    /**
     * Obtiene la lista de precios por marca.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllProductsByBranchId($brand_id): JsonResponse
    {
        try {
            Log::info('getAllProductsByBranchId');
            // Buscar la marca por ID
            $brand = Brand::findOrFail($brand_id);

            // Verificar si la marca está activa
            if (!$brand->active) {
                return response()->json([
                    'message' => "La marca con ID {$brand_id} no está activa.",
                ], 400); // Código 400 para indicar un error del cliente
            }

            // Obtener los productos del repositorio
            $products = $this->cvaRepository->getProductsByBrandId($brand->id);
            $supplierCVA = Supplier::where('name', 'CVA')->first();
            Log::debug($supplierCVA);

            if($supplierCVA){
                foreach ($products['item'] as $productArray) {
                    $product = Product::where('sku', $productArray['codigo_fabricante'])->first();

                    // Actualizar inventario
                    $quantity = $productArray['disponible'];

                    // Crear una estructura de Request
                    $inventoryRequest = ['quantity' => $quantity];
                    if(!$product) {

                        $productRequest = [
                            'name' => $productArray['descripcion'],
                            'cvaKey' => $productArray['clave'],
                            'sku' => $productArray['codigo_fabricante'],
                            'warranty' => $productArray['garantia'],
                            'brandId' => $brand->id,
                            'active' => true

                        ];

                        $productRequest = new Request($productRequest);

                        // Llamada al controlador para crear el producto
                        $productResponse = $this->productController->createProduct($productRequest, $supplierCVA->id);

                        // Obtener el contenido de la respuesta (decodificado como array)
                        $product = json_decode($productResponse->getContent(), true); // Decodifica el JSON a array

                        $productId = $product['id'];
                    }else{
                        $productId = $product->id;
                    }

                    Log::debug($productId);
                    $inventoryRequest = new Request($inventoryRequest);

                    $this->inventoryController->updateInventoryByProductIdAndSupplierId($inventoryRequest, $productId, $supplierCVA->id);

                }
                return response()->json(['message' => 'success'], 200);

            }



            // Devolver la respuesta exitosa
            return response()->json([
                'message' => 'success'
            ]);

        } catch (\Exception $e) {
            // Registrar el error en el log
            Log::error($e->getMessage());

            // Devolver una respuesta de error
            return response()->json([
                'message' => $e->getMessage(),
            ], 422); // Código 422 para otros errores de validación o lógica de negocio
        }
    }
}
