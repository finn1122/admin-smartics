<?php

namespace App\Filament\Resources\DeliveryTypeResource\Pages;

use App\Filament\Resources\DeliveryTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryType extends EditRecord
{
    protected static string $resource = DeliveryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
