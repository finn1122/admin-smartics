<?php

namespace App\Filament\Resources\PaymentProcessorResource\Pages;

use App\Filament\Resources\PaymentProcessorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentProcessor extends EditRecord
{
    protected static string $resource = PaymentProcessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
