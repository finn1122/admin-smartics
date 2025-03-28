<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
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
    // En Product.php
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    // Relación con los datos de proveedores externos
    public function externalProductData()
    {
        return $this->hasMany(ExternalProductData::class);
    }

    public function gallery()
    {
        return $this->hasMany(Gallery::class);
    }

    public function shopCategories(): BelongsToMany
    {
        return $this->belongsToMany(ShopCategory::class, 'shop_category_products', 'product_id', 'category_id')
            ->withPivot(['created_at', 'updated_at']);
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags')
            ->withTimestamps(); // Esto hace que Laravel actualice created_at y updated_at
    }

    // Relación de muchos a muchos con categorías
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    // Obtener la ruta completa del producto
    public function getProductPath()
    {
        // Obtener la categoría más específica del producto
        $category = $this->categories()->orderBy('parent_id', 'desc')->first(); // Tomar la categoría con el parent_id más alto
        return $category ? $category->getFullPath() . '/' . $this->name : $this->name;
    }

}
