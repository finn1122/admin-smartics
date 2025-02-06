<?php

namespace App\Filament\Resources\BatchResource\Pages;

use App\Filament\Resources\BatchResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBatch extends CreateRecord
{
    protected static string $resource = BatchResource::class;

    public function getTitle(): string
    {
        return 'Crear Lote'; // Cambia el título de la página "Create"
    }
}
