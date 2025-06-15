<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/list', [ProductController::class, 'apiIndex']);
Route::post('/contact', [ContactController::class, 'submit']);
Route::post('/message', [ContactController::class, 'message']);
Route::get('/products/{id}', [ProductController::class, 'apiShow']);
Route::get('/banner/{position}', [BannerController::class, 'getBannerByPosition']);
