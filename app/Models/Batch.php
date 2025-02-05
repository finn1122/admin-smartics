<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'quantity',
        'purchase_price',
        'sale_price',
        'purchase_date',
        'purchase_document_url',
    ];

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con el proveedor
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
