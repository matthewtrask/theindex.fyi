<?php

use App\Http\Controllers\Api\IndexController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/indexes', [IndexController::class, 'index'])->name('api.indexes.index');
    Route::get('/indexes/{slug}', [IndexController::class, 'show'])->name('api.indexes.show');
});
