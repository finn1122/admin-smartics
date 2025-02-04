<?php

namespace App\Providers;

use App\Http\Controllers\Api\V1\CVAController;
use App\Http\Controllers\Api\V1\Inventory\InventoryController;
use App\Repositories\CVARepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CVAController::class, function ($app) {
            return new CVAController($app->make(CVARepository::class), $app->make(InventoryController::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
