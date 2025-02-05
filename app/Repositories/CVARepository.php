<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CVARepository
{
    protected string $baseUrl;
    protected string $clienteId;

    public function __construct()
    {
        $this->baseUrl = env('CVA_BASE_URL');
        $this->clienteId = env('CVA_CLIENT');
    }
    /**
     * Obtiene la lista de precios por marca.
     *
     * @param string $marca
     * @return array
     */
    public function getProductsByBrandId(int $brand_id): array
    {
        Log::info('CVARepository@getProductsByBrandId');
        $brand = Brand::findOrFail($brand_id);
        $enpointUrl = 'catalogo_clientes_xml/lista_precios.xml';

        // Verificar si la marca está activa
        if (!$brand->active) {
            throw new \Exception("La marca con ID {$brand_id} no está activa.");
        }

        // Parámetros para la solicitud HTTP
        $params = [
            'cliente' => $this->clienteId,
            'marca' => $brand->name, // Cambiado a $brand->name para coincidir con el ejemplo anterior
            'grupo' => '%',
            'clave' => '%',
            'codigo' => '%',
        ];

        // Hacer la solicitud HTTP
        $XMLResponse = Http::get($this->baseUrl.$enpointUrl, $params);

        // Verificar si la solicitud fue exitosa
        if (!$XMLResponse->successful()) {
            throw new \Exception("Error en la solicitud HTTP. Código de respuesta: " . $XMLResponse->status(), $XMLResponse->status());
        }

        $response = simplexml_load_string($XMLResponse);

        if ($response === false) {
            throw new \Exception("Error al parsear el XML.", 500); // Código 500 para errores del servidor
        }

        $jsonResponse = json_decode(json_encode($response), true);

        return $jsonResponse;
    }
}
