<?php
namespace App\Filament\Resources\CvaApiResource\Pages;

use AllowDynamicProperties;
use App\Filament\Resources\CvaApiResource;
use App\Http\Controllers\Api\V1\CVAController;
use App\Http\Controllers\Api\V1\ExternalProductData\ExternalProductDataController;
use App\Http\Controllers\Api\V1\Inventory\InventoryController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Models\Brand;
use App\Repositories\CVARepository;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;


#[AllowDynamicProperties] class UpdateProductsByBrand extends Page
{
    use InteractsWithForms;

    protected static string $resource = CvaApiResource::class;

    protected static string $view = 'filament.resources.cva-api-resource.pages.update-products-by-brand';

    public $selectedBrand; // Propiedad para almacenar la marca seleccionada
    public function mount()
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('selectedBrand')
                ->label('Seleccionar marca')
                ->options(Brand::where('active', true)->pluck('name', 'id')) // Marcas activas
                ->searchable() // Habilitar búsqueda
                ->required()
                ->placeholder('Buscar marca...'),
        ];
    }

    public function updateProductsByBrand()
    {
        Log::info('updateProductsByBrand');
        // Validar el formulario
        $this->validate([
            'selectedBrand' => 'required|exists:brands,id',
        ]);

        // Obtener la marca seleccionada
        $brand = Brand::find($this->selectedBrand);
        Log::debug($brand);

        try {

            // ✅ Instanciar CVAController aquí para evitar que sea null
            $cvaRepository = app()->make(CVARepository::class);
            $externalProductDataController = app()->make(ExternalProductDataController::class);
            $productController = app()->make(ProductController::class);
            $this->CVAController = new CVAController($cvaRepository, $productController, $externalProductDataController);

            // ✅ Verifica que la instancia no sea null
            if (!$this->CVAController) {
                Log::error('CVAController sigue siendo null');
                throw new \Exception("CVAController no está instanciado correctamente.");
            }


            $response = $this->CVAController->getAllProductsByBranchId($brand->id);

            // Convertir la respuesta en un array
            $responseData = $response->getData(true); // Poner "true" convierte el JSON en un array asociativo

            // Verificar si la respuesta contiene el mensaje de éxito
            if (!empty($responseData['message']) && $responseData['message'] === 'success') {
                // Notificación de éxito
                Notification::make()
                    ->title('Actualización exitosa')
                    ->body("Los productos de la marca {$brand->name} han sido actualizados correctamente.")
                    ->success()
                    ->send();
            } else {
                // Notificación de error si la respuesta no es exitosa
                Notification::make()
                    ->title('Error')
                    ->body('No se pudo actualizar los productos. Por favor, inténtalo de nuevo.')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            // Notificación de error si ocurre una excepción
            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al intentar actualizar los productos.')
                ->danger()
                ->send();
        }

        // Limpiar el formulario después de la acción
        $this->form->fill();
    }
}
