<?php

namespace App\Providers;

use App\Features\Ftp\Data\Repositories\FtpRepositoryImpl;
use App\Features\Ftp\Domain\Repositories\FtpRepositoryInterface;
use App\Filament\Forms\Components\PolygonMap;
use App\Http\Controllers\Api\V1\CVAController;
use App\Http\Controllers\Api\V1\ExternalProductData\ExternalProductDataController;
use App\Http\Controllers\Api\V1\Product\ProductController;
use App\Livewire\GalleryModal;
use App\Livewire\PolygonMapInput;
use App\Repositories\CVARepository;
use App\Services\SendinblueService;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*$this->app->singleton(SendinblueService::class, function ($app) {
            return new SendinblueService();
        });*/
        Livewire::component('polygon-map-input', PolygonMapInput::class);


        $this->app->singleton(CVAController::class, function ($app) {
            return new CVAController($app->make(CVARepository::class), $app->make(ProductController::class), $app->make(ExternalProductDataController::class));
        });

        // Vincula la interfaz con su implementación
        $this->app->bind(FtpRepositoryInterface::class, FtpRepositoryImpl::class);

        // Registrar el macro para usar más fácilmente
        \Filament\Forms\Components\Field::macro('polygonMap', function (string $height = '500px') {
            return PolygonMap::make($this->name)
                ->height($height);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('polygon-map-input', \App\Livewire\PolygonMapInput::class);

        \Filament\Forms\Components\Field::macro('polygonMap', function(string $height = '500px') {
            return \App\Filament\Forms\Components\PolygonMap::make($this->name)
                ->height($height);
        });
    }
}
