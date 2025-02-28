<?php

namespace App\Jobs\CVAJob;

use App\Http\Controllers\Api\V1\Product\ProductController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productData;
    protected $supplierId;

    public function __construct(array $productData, int $supplierId)
    {
        $this->productData = $productData;
        $this->supplierId = $supplierId;
    }

    public function handle(ProductController $productController)
    {
        $productRequest = new Request($this->productData);
        $productController->createProduct($productRequest, $this->supplierId);
    }
}
