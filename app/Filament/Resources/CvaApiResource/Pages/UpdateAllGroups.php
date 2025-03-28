<?php
namespace App\Filament\Resources\CvaApiResource\Pages;

use AllowDynamicProperties;
use App\Filament\Resources\CvaApiResource;
use App\Http\Controllers\Api\V1\CVAController;
use App\Http\Controllers\Api\V1\ExternalProductData\ExternalProductDataController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Models\Brand;
use App\Repositories\CVARepository;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

#[AllowDynamicProperties] class UpdateAllGroups extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = CvaApiResource::class;
    protected static string $view = 'filament.resources.cva-api-resource.pages.update-all-groups';
    // Definir el formulario
    protected function getFormSchema(): array
    {
        return [
            Section::make('Actualizar todos los grupos')
        ];
    }

    // Definir las acciones para los botones
    protected function getActions(): array
    {
        return [
            Action::make('updateAllGroups') // Acción para actualizar todos los productos
            ->label('Actualizar')
                ->color('primary') // Color del botón
                ->action('updateAllGroups') // Método de acción
        ];
    }

    // Método para ejecutar la actualización
    public function updateAllGroups()
    {
        Log::info('updateAllGroups');

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


            $response = $this->CVAController->getAllCvaGroups();

            // Convertir la respuesta en un array
            $responseData = $response->getData(true); // Poner "true" convierte el JSON en un array asociativo

            Log::debug($responseData);

            // Verificar si la respuesta contiene el mensaje de éxito
            if (!empty($responseData['message']) && $responseData['message'] === 'success') {
                // Notificación de éxito
                Notification::make()
                    ->title('Actualización exitosa')
                    ->body("Todos los productos han sido actualizados correctamente.")
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
    }
}
