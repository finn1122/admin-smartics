<?php

namespace App\Imports;

use App\Models\InegiCity;
use App\Models\InegiMunicipality;
use App\Models\InegiPostalData;
use App\Models\InegiState;
use App\Models\InegiSettlementType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InegiDataImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    private $rowCount = 0;

    public function model(array $row)
    {
        $this->rowCount++;

        // 1. Estado
        $state = InegiState::firstOrCreate([
            'c_estado' => $row['c_estado'],
        ], [
            'd_estado' => $row['d_estado'],
        ]);

        // 2. Municipio
        $municipality = InegiMunicipality::firstOrCreate([
            'c_estado' => $row['c_estado'],
            'c_mnpio' => $row['c_mnpio'],
        ], [
            'D_mnpio' => $row['D_mnpio'],
        ]);

        // 3. Ciudad (si existe)
        if (!empty($row['c_cve_ciudad'])) {
            $city = InegiCity::firstOrCreate([
                'c_cve_ciudad' => $row['c_cve_ciudad'],
                'c_estado' => $row['c_estado'],
                'c_mnpio' => $row['c_mnpio'],
            ], [
                'd_ciudad' => $row['d_ciudad'] ?? null,
            ]);
        }

        // 4. Tipo de asentamiento
        $settlementType = InegiSettlementType::firstOrCreate([
            'c_tipo_asenta' => $row['c_tipo_asenta'],
        ], [
            'd_tipo_asenta' => $row['d_tipo_asenta'],
        ]);

        // 5. Datos postales
        return new InegiPostalData([
            'd_codigo' => $row['d_codigo'],
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
            'id_asenta_cpcons' => $row['id_asenta_cpcons'],
            'd_zona' => $row['d_zona'],
            'c_cve_ciudad' => $row['c_cve_ciudad'] ?? null,
            'latitud' => $row['latitud'] ?? null,
            'longitud' => $row['longitud'] ?? null,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            'd_codigo' => 'required|digits:5',
            'd_asenta' => 'required',
            'c_estado' => 'required|size:2',
            'c_mnpio' => 'required|size:3',
            'id_asenta_cpcons' => 'required|size:4',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
