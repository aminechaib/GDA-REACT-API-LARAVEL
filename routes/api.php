<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FournisseurController;
// Add this line to your api.php
Route::post('products/import', [ProductController::class, 'import']);

// We will add more routes here in the next steps
Route::apiResource('products', ProductController::class)->only(['index']);

Route::get('fournisseurs', [\App\Http\Controllers\Api\FournisseurController::class, 'index']);
Route::post('fournisseurs', [\App\Http\Controllers\Api\FournisseurController::class, 'store']);
Route::post('fournisseurs/seed', [\App\Http\Controllers\Api\FournisseurController::class, 'seed']);

// Import mapping endpoint
Route::post('products/import-mapping', [ProductController::class, 'importMapping']);

Route::post('products-simple', [ProductController::class, 'simpleIndex']);
