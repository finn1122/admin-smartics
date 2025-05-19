<?php

namespace App\Filament\Resources\InegiImportResource\Pages;

use App\Filament\Resources\InegiImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInegiImport extends EditRecord
{
    protected static string $resource = InegiImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
