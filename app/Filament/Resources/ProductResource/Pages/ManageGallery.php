<?php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\Page;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ManageGallery extends Page
{
    protected static string $resource = ProductResource::class;
    protected static string $view = 'filament.resources.product-resource.pages.manage-gallery';

    public Product $record;

    public function mount(Product $record): void
    {
        Log::info('Montando ManageGallery', ['record' => $record]);

        if (!$record || !$record->id) {
            Log::error('El ID del producto no se recibió correctamente.');
        }

        $this->record = Product::with('gallery')->findOrFail($record->id);

        Log::info('Producto encontrado', ['product_id' => $this->record->id]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Aquí puedes agregar acciones adicionales si es necesario
        ];
    }
}
