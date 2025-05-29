<?php

namespace App\Filament\Resources\PaymentProcessorResource\Pages;

use App\Filament\Resources\PaymentProcessorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentProcessor extends CreateRecord
{
    protected static string $resource = PaymentProcessorResource::class;
}
