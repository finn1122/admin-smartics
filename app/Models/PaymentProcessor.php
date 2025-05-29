<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentProcessor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'processor_client_id',
        'processor_key',
        'description',
        'email',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
