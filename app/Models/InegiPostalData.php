<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InegiPostalData extends Model
{
    protected $table = 'inegi_postal_data';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'd_codigo',
        'd_asenta',
        'd_tipo_asenta',
        'D_mnpio',
        'd_estado',
        'd_ciudad',
        'd_CP',
        'c_estado',
        'c_oficina',
        'c_CP',
        'c_tipo_asenta',
        'c_mnpio',
        'id_asenta_cpcons',
        'd_zona',
        'c_cve_ciudad',
        'latitud',
        'longitud'
    ];

    // Relaciones actualizadas
    public function state(): BelongsTo
    {
        return $this->belongsTo(InegiState::class, 'state_id');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(InegiMunicipality::class, 'municipality_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(InegiCity::class, 'city_id');
    }

    public function settlementType(): BelongsTo
    {
        return $this->belongsTo(InegiSettlementType::class, 'settlement_type_id');
    }
}
