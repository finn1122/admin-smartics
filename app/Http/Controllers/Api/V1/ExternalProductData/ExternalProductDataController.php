<?php

namespace App\Http\Controllers\Api\V1\ExternalProductData;

use App\Http\Controllers\Controller;
use App\Models\ExternalProductData;
use App\Models\Product;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalProductDataController extends Controller
{
    function updateExternalProductData(int $product_id, $supplier_id, string $currency_code, float $price, int $quantity)
    {
        Log::info('updateExternalProductData');
        $product = Product::findOrFail($product_id);
        $supplier = Supplier::findOrFail($supplier_id);

        ExternalProductData::updateOrCreate(
            [
                'product_id' => $product_id,
                'supplier_id' => $supplier_id
            ], // Criterios de búsqueda
            [
                'price' => $price,
                'currency_code' => $currency_code,
                'quantity' => $quantity,
                'consulted_at' => Carbon::now(),
            ] // Datos a actualizar o crear
        );
    }
}
