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
        'active',
        'payment_processor_id'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Obtiene el procesador de pago asociado
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(PaymentProcessor::class);
    }

    /**
     * Scope para métodos activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para buscar por código
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Verifica si el método está completamente habilitado
     * (activo y con procesador activo/configurado)
     */
    public function getIsFullyActiveAttribute(): bool
    {
        return $this->active;
    }
}
