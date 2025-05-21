<?php

namespace App\Filament\Imports;

use App\Models\InegiCity;
use App\Models\InegiMunicipality;
use App\Models\InegiPostalData;
use App\Models\InegiSettlementType;
use App\Models\InegiState;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;

class InegiPostalDataImporter extends Importer
{
    protected static ?string $model = InegiPostalData::class;

    public static function getColumns(): array
    {
        Log::info('InegiPostalDataImporter@getColumns');
        return [
            ImportColumn::make('d_codigo')
                ->label('Código Postal')
                ->requiredMapping()
                ->rules(['required', 'max:10']),

            ImportColumn::make('d_asenta')
                ->label('Asentamiento')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('d_tipo_asenta')
                ->label('Tipo de Asentamiento')
                ->requiredMapping()
                ->rules(['required', 'max:100']),

            ImportColumn::make('D_mnpio')
                ->label('Municipio')
                ->requiredMapping()
                ->rules(['required', 'max:150']),

            ImportColumn::make('d_estado')
                ->label('Estado')
                ->requiredMapping()
                ->rules(['required', 'max:100']),

            ImportColumn::make('d_ciudad')
                ->label('Ciudad')
                ->rules(['max:150']),

            ImportColumn::make('d_CP')
                ->label('CP (Texto)')
                ->rules(['max:10']),

            ImportColumn::make('c_estado')
                ->label('Clave Estado')
                ->requiredMapping()
                ->rules(['required', 'max:2']),

            ImportColumn::make('c_oficina')
                ->label('Clave Oficina')
                ->rules(['max:5']),

            ImportColumn::make('c_CP')
                ->label('Clave CP')
                ->rules(['max:5']),

            ImportColumn::make('c_tipo_asenta')
                ->label('Clave Tipo Asentamiento')
                ->requiredMapping()
                ->rules(['required', 'max:3']),

            ImportColumn::make('c_mnpio')
                ->label('Clave Municipio')
                ->requiredMapping()
                ->rules(['required', 'max:3']),

            ImportColumn::make('id_asenta_cpcons')
                ->label('ID Asentamiento')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),

            ImportColumn::make('d_zona')
                ->label('Zona')
                ->rules(['max:50']),

            ImportColumn::make('c_cve_ciudad')
                ->label('Clave Ciudad')
                ->rules(['max:5']),
        ];
    }

    public function resolveRecord(): ?InegiPostalData
    {
        Log::info('InegiPostalDataImporter@resolveRecord');

        // Primero asegurar que existan las relaciones
        $this->ensureRelationsExist();

        return InegiPostalData::firstOrNew([
            'd_codigo' => $this->data['d_codigo'],
            'id_asenta_cpcons' => $this->data['id_asenta_cpcons'],
        ]);
    }

    protected function ensureRelationsExist(): void
    {
        Log::info('InegiPostalDataImporter@ensureRelationsExist');

        // 1. Estado
        InegiState::firstOrCreate([
            'c_estado' => $this->data['c_estado'],
        ], [
            'd_estado' => $this->data['d_estado'],
            'abrev' => substr($this->data['d_estado'], 0, 3),
        ]);

        // 2. Municipio
        InegiMunicipality::firstOrCreate([
            'c_estado' => $this->data['c_estado'],
            'c_mnpio' => $this->data['c_mnpio'],
        ], [
            'D_mnpio' => $this->data['D_mnpio'],
        ]);

        // 3. Tipo de Asentamiento
        InegiSettlementType::firstOrCreate([
            'c_tipo_asenta' => $this->data['c_tipo_asenta'],
        ], [
            'd_tipo_asenta' => $this->data['d_tipo_asenta'],
        ]);

        // 4. Ciudad (si existe el campo)
        if (!empty($this->data['c_cve_ciudad'])) {
            InegiCity::firstOrCreate([
                'c_cve_ciudad' => $this->data['c_cve_ciudad'],
            ], [
                'd_ciudad' => $this->data['d_ciudad'] ?? null,
                'c_estado' => $this->data['c_estado'],
                'c_mnpio' => $this->data['c_mnpio'],
            ]);
        }
    }

    public function afterCreate(InegiPostalData $record, array $row): void
    {
        Log::info('InegiPostalDataImporter@afterCreate');
        // Lógica para después de crear cada registro
        $record->fill([
            'd_asenta' => $row['d_asenta'],
            'd_tipo_asenta' => $row['d_tipo_asenta'],
            'D_mnpio' => $row['D_mnpio'],
            'd_estado' => $row['d_estado'],
            'd_ciudad' => $row['d_ciudad'] ?? null,
            'd_CP' => $row['d_CP'] ?? null,
            'c_estado' => $row['c_estado'],
            'c_oficina' => $row['c_oficina'] ?? null,
            'c_CP' => $row['c_CP'] ?? null,
            'c_tipo_asenta' => $row['c_tipo_asenta'],
            'c_mnpio' => $row['c_mnpio'],
            'd_zona' => $row['d_zona'] ?? null,
            'c_cve_ciudad' => $row['c_cve_ciudad'] ?? null,
        ]);

        $record->save();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        Log::info('InegiPostalDataImporter@getCompletedNotificationBody');

        $body = 'La importación de códigos postales ha finalizado. ';
        $body .= number_format($import->successful_rows) . ' ' . str('registro')->plural($import->successful_rows) . ' importados correctamente.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('registro')->plural($failedRowsCount) . ' fallaron.';
        }

        return $body;
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            // Puedes agregar componentes adicionales para opciones de importación
            // Por ejemplo, para manejar duplicados o validaciones adicionales
        ];
    }
}
