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
    /**
     * Obtiene todos los productos
     * @return array
     */
    public function getAllProducts(): array
    {
        Log::info('CVARepository@getAllProducts');
        $enpointUrl = 'catalogo_clientes_xml/lista_precios.xml';

        // Parámetros para la solicitud HTTP
        $params = [
            'cliente' => $this->clienteId,
            'marca' => '%',
            'grupo' => '%',
            'clave' => '%',
            'codigo' => '%',
        ];

        Log::debug('Solicitando datos desde: ' . $this->baseUrl . $enpointUrl, $params);

        try {
            // Hacer la solicitud HTTP con un timeout de 120 segundos
            $XMLResponse = Http::timeout(120)->get($this->baseUrl . $enpointUrl, $params);

            // Verificar si la solicitud fue exitosa
            if (!$XMLResponse->successful()) {
                throw new \Exception("Error en la solicitud HTTP. Código de respuesta: " . $XMLResponse->status(), $XMLResponse->status());
            }

            // Parsear la respuesta XML
            $response = simplexml_load_string($XMLResponse->body());

            if ($response === false) {
                throw new \Exception("Error al parsear el XML.", 500);
            }

            // Convertir el XML a un array
            $jsonResponse = json_decode(json_encode($response), true);

            return $jsonResponse;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Timeout al realizar la solicitud HTTP: ' . $e->getMessage());
            throw new \Exception("La solicitud HTTP tardó demasiado en completarse. Por favor, inténtalo de nuevo más tarde.", 504);
        } catch (\Exception $e) {
            Log::error('Error en getAllProducts: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Obtiene todos los grupos
     * @return array
     */
    public function getAllGroups(): array
    {
        Log::info('CVARepository@getAllGroups');
        $enpointUrl = 'catalogo_clientes_xml/grupos2.xml';

        Log::debug('Solicitando datos desde: ' . $this->baseUrl . $enpointUrl);

        try {
            // Hacer la solicitud HTTP con un timeout de 120 segundos
            $XMLResponse = Http::timeout(120)->get($this->baseUrl . $enpointUrl);

            // Verificar si la solicitud fue exitosa
            if (!$XMLResponse->successful()) {
                throw new \Exception("Error en la solicitud HTTP. Código de respuesta: " . $XMLResponse->status(), $XMLResponse->status());
            }

            // Parsear la respuesta XML
            $response = simplexml_load_string($XMLResponse->body());

            if ($response === false) {
                throw new \Exception("Error al parsear el XML.", 500);
            }

            // Convertir el XML a un array
            $jsonResponse = json_decode(json_encode($response), true);

            return $jsonResponse;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Timeout al realizar la solicitud HTTP: ' . $e->getMessage());
            throw new \Exception("La solicitud HTTP tardó demasiado en completarse. Por favor, inténtalo de nuevo más tarde.", 504);
        } catch (\Exception $e) {
            Log::error('Error en getAllProducts: ' . $e->getMessage());
            throw $e;
        }
    }

}
