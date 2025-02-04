<?php
namespace App\Filament\Resources\CvaApiResource\Pages;

use App\Filament\Resources\CvaApiResource;
use App\Models\Brand;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class UpdateAllProducts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = CvaApiResource::class;
    protected static string $view = 'filament.resources.cva-api-resource.pages.update-all-products';
    // Definir el formulario
    protected function getFormSchema(): array
    {
        return [
            Section::make('Actualizar todos los productos')
        ];
    }

    // Definir las acciones para los botones
    protected function getActions(): array
    {
        return [
            Action::make('updateAllProducts') // Acción para actualizar todos los productos
            ->label('Actualizar')
                ->color('primary') // Color del botón
                ->action('updateAllProducts') // Método de acción
        ];
    }

    // Método para ejecutar la actualización
    public function updateAllProducts()
    {
        Log::info('updateAllProducts');



        // Lógica de actualización de productos
        // Enviar una notificación de éxito
        Notification::make()
            ->title('Actualización exitosa')
            ->body('Todos los productos han sido actualizados correctamente.')
            ->success()
            ->send();
    }
}
