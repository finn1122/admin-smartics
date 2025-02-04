<?php
namespace App\Http\Controllers\Api\V1\Inventory;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\error;

class InventoryController extends Controller
{
    public function updateInventoryByProductIdAndSupplierId(Request $request, $product_id, $supplier_id): JsonResponse
    {
        try {
            Log::info('updateInventoryByProductIdAndSupplierId');
            // Validación de los datos del request
            $validatedData = $request->validate([
                'quantity'  => 'required|integer',
            ]);

            $quantity = $validatedData['quantity'];

            // Actualizar Inventario
            Inventory::updateOrCreate(
                ['product_id' => $product_id, 'supplier_id' => $supplier_id], // Criterios de búsqueda
                ['quantity' => $quantity] // Datos a actualizar o crear
            );


            return response()->json(['message' => 'success'], 200);

        }catch (\Exception $e){
            Log::error($e->getMessage());
        }
    }
}
