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


    // Validación adicional para las coordenadas
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'active' => 'required|boolean',
            'description' => 'nullable|string',
            'coordinates' => ['required', 'array'],
            'coordinates.type' => ['required', 'in:Polygon'],
            'coordinates.coordinates' => ['required', 'array', 'min:1'],
        ];
    }
}
