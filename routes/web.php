<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\DeliveryAreaController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/gallery/upload/{productId}', [\App\Http\Controllers\Api\V1\Product\ProductGalleryController::class, 'uploadImage'])->name('gallery.upload');
Route::delete('/gallery/delete/{imageId}', [\App\Http\Controllers\Api\V1\Product\ProductGalleryController::class, 'deleteImage'])->name('gallery.delete');

Route::get('/delivery-areas/create', [DeliveryAreaController::class, 'create'])->name('delivery-areas.create');
Route::post('/delivery-areas', [DeliveryAreaController::class, 'store'])->name('delivery-areas.store');
