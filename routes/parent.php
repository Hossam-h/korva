<?php

use App\Http\Controllers\Api\Player\ParentChildController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['api', 'auth:player'],
    'prefix' => 'parent',
], function () {
    Route::get('children', [ParentChildController::class, 'index']);
    Route::post('children', [ParentChildController::class, 'store']);
    Route::put('children/{child}', [ParentChildController::class, 'update']);
    Route::delete('children/{child}', [ParentChildController::class, 'destroy']);
});
