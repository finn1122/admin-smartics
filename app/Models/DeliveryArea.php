<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];


    protected $fillable = [
        'name',         // Nombre del área
        'price',       // Precio de envío
        'coordinates', // Polígono GeoJSON
        'active',   // Estado activo/inactivo
        'description'  // Descripción adicional
    ];
    protected $casts = [
        'coordinates' => 'array', // Conversión automática JSON <> array
        'active' => 'boolean'  // Conversión para el toggle
    ];

    // Método para asegurar formato correcto al guardar
    public function setCoordinatesAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['coordinates'] = null;
            return;
        }

        // Asegurar que es un polígono GeoJSON válido
        $this->attributes['coordinates'] = [
            'type' => 'Polygon',
            'coordinates' => $value['coordinates'] ?? $value
        ];
    }

    public static function rules(): array
    {
        return [
            'coordinates' => ['required', 'array'],
            'coordinates.type' => ['required', 'in:Polygon'],
            'coordinates.coordinates' => ['required', 'array'],
        ];
    }
}
