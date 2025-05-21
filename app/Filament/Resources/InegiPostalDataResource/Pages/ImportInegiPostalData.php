<?php

namespace App\Filament\Resources\InegiPostalDataResource\Pages;

use App\Filament\Resources\InegiPostalDataResource;
use App\Models\InegiCity;
use App\Models\InegiMunicipality;
use App\Models\InegiPostalData;
use App\Models\InegiSettlementType;
use App\Models\InegiState;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class ImportInegiPostalData extends Page
{
    protected static string $resource = InegiPostalDataResource::class;

    protected static ?string $title = 'Importar Códigos Postales';
    protected static string $view = 'filament.resources.inegi-postal-data-resource.pages.import';

    public ?array $data = [];

    // Métodos requeridos para evitar errores
    public function getCachedFormActions(): array
    {
        return $this->getFormActions();
    }

    public function getFormActions(): array
    {
        return $this->getHeaderActions();
    }

    public function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file')
                    ->label('Archivo Excel del INEGI')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel'
                    ])
                    ->disk('local')
                    ->directory('inegi-imports')
                    ->preserveFilenames()
                    ->maxSize(10240)
                    ->helperText('Selecciona el archivo con los códigos postales'),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importar Datos')
                ->action('import')
                ->color('primary')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }

    public function import(): void
    {
        Log::info('ImportInegiPostalData@import');
        $data = $this->form->getState();
        $filePath = Storage::disk('local')->path($data['file']);

        DB::beginTransaction();  // Inicio transacción

        try {
            Excel::import(new class implements ToModel, WithHeadingRow {
                public function model(array $row)
                {
                    Log::debug('Procesando fila:', $row);

                    // Validar campos requeridos
                    if (empty($row['d_codigo']) || empty($row['id_asenta_cpcons'])) {
                        Log::warning('Campos requeridos faltantes', $row);
                        return null;
                    }

                    $settlementType = InegiSettlementType::firstOrCreate(
                        ['c_tipo_asenta' => $row['c_tipo_asenta']],
                        [
                            'd_tipo_asenta' => $row['d_tipo_asenta'],
                            'short_name' => $this->generateShortName($row['d_tipo_asenta'])
                        ]
                    );

                    $state = InegiState::firstOrCreate(
                        ['c_estado' => $row['c_estado']],
                        [
                            'd_estado' => $row['d_estado'],
                            'abrev' => substr($row['d_estado'], 0, 3)
                        ]
                    );

                    $municipality = InegiMunicipality::firstOrCreate(
                        ['c_estado' => $row['c_estado'], 'c_mnpio' => $row['c_mnpio']],
                        ['D_mnpio' => $row['d_mnpio']]
                    );

                    $city = !empty($row['c_cve_ciudad']) ? InegiCity::firstOrCreate(
                        ['c_cve_ciudad' => $row['c_cve_ciudad']],
                        [
                            'd_ciudad' => $row['d_ciudad'] ?? $row['d_asenta'],
                            'c_estado' => $row['c_estado'],
                            'c_mnpio' => $row['c_mnpio'],
                            'es_capital' => false
                        ]
                    ) : null;

                    return InegiPostalData::updateOrCreate(
                        [
                            'd_codigo' => $row['d_codigo'],
                            'id_asenta_cpcons' => $row['id_asenta_cpcons']
                        ],
                        [
                            'd_asenta' => $row['d_asenta'],
                            'd_tipo_asenta' => $row['d_tipo_asenta'],
                            'D_mnpio' => $row['d_mnpio'],
                            'd_estado' => $row['d_estado'],
                            'd_ciudad' => $row['d_ciudad'] ?? null,
                            'd_zona' => $row['d_zona'],
                            'c_estado' => $row['c_estado'],
                            'c_mnpio' => $row['c_mnpio'],
                            'c_tipo_asenta' => $row['c_tipo_asenta'],
                            'c_cve_ciudad' => $row['c_cve_ciudad'] ?? null,
                            'c_oficina' => $row['c_oficina'] ?? null,
                            'latitud' => $row['latitud'] ?? null,
                            'longitud' => $row['longitud'] ?? null,
                            'state_id' => $state->id,
                            'municipality_id' => $municipality->id,
                            'settlement_type_id' => $settlementType->id,
                            'city_id' => $city?->id
                        ]
                    );
                }

                protected function generateShortName(string $fullName): string
                {
                    $words = explode(' ', $fullName);
                    $short = '';
                    foreach ($words as $word) {
                        $short .= strtoupper(substr($word, 0, 1));
                    }
                    return substr($short, 0, 5);
                }
            }, $filePath);

            DB::commit(); // Confirmar cambios si todo fue bien

            Notification::make()
                ->title('Importación exitosa')
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollback(); // Revertir cambios si hubo error
            Log::error('Error en importación: ' . $e->getMessage());
            Notification::make()
                ->title('Error en la importación')
                ->body("Error: " . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
