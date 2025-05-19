<?php

namespace App\Filament\Resources\InegiImportResource\Pages;

use App\Filament\Resources\InegiImportResource;
use App\Imports\InegiDataImport;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportInegiData extends Page
{
    protected static string $resource = InegiImportResource::class;
    protected static string $view = 'filament.resources.inegi-import-resource.pages.import-inegi-data';

    public $file;
    public $truncate = false;
    public $imported = false;
    public $rowCount = 0;

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        if ($this->truncate) {
            $this->truncateTables();
        }

        $import = new InegiDataImport();
        Excel::import($import, Storage::path($this->file));

        $this->rowCount = $import->getRowCount();
        $this->imported = true;

        $this->notify('success', "Se importaron {$this->rowCount} registros correctamente");
    }

    protected function truncateTables(): void
    {
        \App\Models\InegiPostalData::truncate();
        \App\Models\InegiCity::truncate();
        \App\Models\InegiMunicipality::truncate();
        \App\Models\InegiState::truncate();
        \App\Models\InegiSettlementType::truncate();

        $this->notify('info', 'Datos anteriores eliminados');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Procesar Importación')
                ->action('import')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('primary'),
        ];
    }
}
