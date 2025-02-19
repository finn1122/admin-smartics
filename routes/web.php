<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/gallery/upload/{productId}', [\App\Http\Controllers\Api\V1\Product\ProductGalleryController::class, 'uploadImage'])->name('gallery.upload');
Route::delete('/gallery/delete/{imageId}', [\App\Http\Controllers\Api\V1\Product\ProductGalleryController::class, 'deleteImage'])->name('gallery.delete');
