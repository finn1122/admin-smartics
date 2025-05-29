<?php

namespace App\Filament\Resources\PaymentProcessorResource\Pages;

use App\Filament\Resources\PaymentProcessorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentProcessors extends ListRecords
{
    protected static string $resource = PaymentProcessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
