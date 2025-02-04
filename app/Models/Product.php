<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cva_key',
        'sku',
        'warranty',
        'brand_id',
        'product_type',
        'active',
    ];

    /**
     * Obtener la marca asociada al producto.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Obtener el grupo asociado al producto.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Obtener los proveedores asociados a este producto.
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'inventories', 'product_id', 'supplier_id')
            ->withPivot('quantity', 'purchase_date');
    }

    // Relación con la tabla galleries (uno a muchos)
    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }
}
