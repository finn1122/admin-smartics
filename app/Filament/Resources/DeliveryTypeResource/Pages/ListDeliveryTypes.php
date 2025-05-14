<?php

namespace App\Filament\Resources\DeliveryTypeResource\Pages;

use App\Filament\Resources\DeliveryTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryTypes extends ListRecords
{
    protected static string $resource = DeliveryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
