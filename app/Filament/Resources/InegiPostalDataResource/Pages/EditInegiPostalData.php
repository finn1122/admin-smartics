<?php

namespace App\Filament\Resources\InegiPostalDataResource\Pages;

use App\Filament\Resources\InegiPostalDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInegiPostalData extends EditRecord
{
    protected static string $resource = InegiPostalDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
