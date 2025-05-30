<?php

namespace App\Models;

use App\Services\PayPalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class PaymentProcessor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Obtiene los métodos de pago asociados
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Scope para procesadores activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Obtiene las credenciales desde .env
     */
    public function getCredentialsAttribute(): ?array
    {
        return match($this->type) {
            'paypal' => [
                'client_id' => env('PAYPAL_CLIENT_ID'),
                'secret' => env('PAYPAL_SECRET'),
                'mode' => env('PAYPAL_MODE', 'sandbox'),
            ],
            'stripe' => [
                'key' => env('STRIPE_KEY'),
                'secret' => env('STRIPE_SECRET'),
            ],
            default => null,
        };
    }

    /**
     * Verifica si las credenciales están configuradas
     */
    public function getIsConfiguredAttribute(): bool
    {
        $creds = $this->credentials;

        if (!$creds) return false;

        return match($this->type) {
            'paypal' => !empty($creds['client_id']) && !empty($creds['secret']),
            'stripe' => !empty($creds['key']) && !empty($creds['secret']),
            default => false,
        };
    }
}
