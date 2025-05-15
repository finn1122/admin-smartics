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
}
