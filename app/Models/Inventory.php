<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', // ID del producto (clave foránea)
        'supplier_id',
        'quantity', // Cantidad en inventario
        'purchase_date', // Fecha de compra
    ];

    /**
     * Obtener el producto asociado al inventario.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtener el proveedor asociado al inventario.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
