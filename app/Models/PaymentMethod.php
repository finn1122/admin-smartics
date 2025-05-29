<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'method_type',
        'instructions',
        'payment_processor_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function processor(): BelongsTo
    {
        return $this->belongsTo(PaymentProcessor::class, 'payment_processor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
