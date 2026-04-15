<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FournisseurController;
use App\Http\Controllers\Api\AuthController;
// Add this line to your api.php
Route::post('products/import', [ProductController::class, 'import']);

// We will add more routes here in the next steps
Route::apiResource('products', ProductController::class)->only(['index']);

Route::get('fournisseurs', [\App\Http\Controllers\Api\FournisseurController::class, 'index']);
Route::post('fournisseurs', [\App\Http\Controllers\Api\FournisseurController::class, 'store']);
Route::post('fournisseurs/seed', [\App\Http\Controllers\Api\FournisseurController::class, 'seed']);

// Import mapping endpoint
Route::post('products/import-mapping', [ProductController::class, 'importMapping']);

// Export endpoint for filtered/all products
Route::get('products/export', [ProductController::class, 'export']);

Route::post('products-simple', [ProductController::class, 'simpleIndex']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Protected data routes
    Route::post('products/import', [ProductController::class, 'import']);
    Route::apiResource('products', ProductController::class)->only(['index']);
    Route::post('products/import-mapping', [ProductController::class, 'importMapping']);
    Route::get('products/export', [ProductController::class, 'export']);
    Route::post('products-simple', [ProductController::class, 'simpleIndex']);

    Route::get('fournisseurs', [FournisseurController::class, 'index']);
    Route::post('fournisseurs', [FournisseurController::class, 'store']);
    Route::post('fournisseurs/seed', [FournisseurController::class, 'seed']);
});

