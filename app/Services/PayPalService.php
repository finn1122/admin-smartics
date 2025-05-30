<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayPalService
{
    public function __construct(
        protected string $clientId,
        protected string $secret,
        protected string $mode = 'sandbox'
    ) {}

    public function getBaseUrl(): string
    {
        return $this->mode === 'live'
            ? 'https://api.paypal.com'
            : 'https://api.sandbox.paypal.com';
    }

    public function createOrder(array $orderData): array
    {
        $response = Http::withBasicAuth($this->clientId, $this->secret)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])
            ->post($this->getBaseUrl().'/v2/checkout/orders', $orderData);

        return $response->json();
    }
}
