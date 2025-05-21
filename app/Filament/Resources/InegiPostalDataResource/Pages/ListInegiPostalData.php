<?php

namespace App\Filament\Resources\InegiPostalDataResource\Pages;

use App\Filament\Resources\InegiPostalDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Excel;


class ListInegiPostalData extends ListRecords
{
    protected static string $resource = InegiPostalDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Importar desde Excel')
                ->url(fn () => InegiPostalDataResource::getUrl('import'))
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->button()
                ->outlined(),
        ];
    }
}
