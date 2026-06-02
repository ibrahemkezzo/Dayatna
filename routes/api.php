<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EntityController;
use App\Http\Controllers\Api\ImageManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);
});

// Protected Administrative Routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('categories')->group(function () {
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});

// Publicly available ecosystem nodes
Route::prefix('entities')->group(function () {
    Route::get('/', [EntityController::class, 'index']);
    Route::get('/{id}', [EntityController::class, 'show']);
});

// Administrative or Authorized Owner Operations
Route::middleware(['auth:sanctum'])->prefix('entities')->group(function () {
    Route::post('/', [EntityController::class, 'store']);
    Route::put('/{id}', [EntityController::class, 'update']);
    Route::delete('/{id}', [EntityController::class, 'destroy']);
});


Route::prefix('images')->name('images.')->group(function () {

    // Route for deleting a specific image by its ID
    Route::delete('{id}', [ImageManagementController::class, 'destroy'])->name('destroy');

    // Route for promoting an image to be the primary asset
    Route::patch('{id}/make-primary', [ImageManagementController::class, 'makePrimary'])->name('make-primary');

});
include __DIR__.'/auth.php';
