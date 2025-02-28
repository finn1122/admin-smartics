<?php

namespace App\Jobs\CVAJob;

use App\Http\Controllers\Api\V1\ExternalProductData\ExternalProductDataController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateExternalProductDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productId;
    protected $supplierId;
    protected $currencyCode;
    protected $price;
    protected $quantity;

    public function __construct(int $productId, int $supplierId, string $currencyCode, float $price, int $quantity)
    {
        $this->productId = $productId;
        $this->supplierId = $supplierId;
        $this->currencyCode = $currencyCode;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function handle(ExternalProductDataController $externalProductDataController)
    {
        $externalProductDataController->updateExternalProductData(
            $this->productId,
            $this->supplierId,
            $this->currencyCode,
            $this->price,
            $this->quantity
        );
    }
}
