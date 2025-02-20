<?php

namespace App\Providers;

use App\Features\Ftp\Data\Repositories\FtpRepositoryImpl;
use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use App\Http\Controllers\Api\V1\CVAController;
use App\Http\Controllers\Api\V1\ExternalProductData\ExternalProductDataController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Repositories\CVARepository;
use App\Services\SendinblueService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SendinblueService::class, function ($app) {
            return new SendinblueService();
        });

        $this->app->singleton(CVAController::class, function ($app) {
            return new CVAController($app->make(CVARepository::class), $app->make(ProductController::class), $app->make(ExternalProductDataController::class));
        });

        // Vincula la interfaz con su implementación
        $this->app->bind(FtpRepositoryInterface::class, FtpRepositoryImpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
