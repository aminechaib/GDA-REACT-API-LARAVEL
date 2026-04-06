<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

// Add this line to your api.php
Route::post('products/import', [ProductController::class, 'import']);

// We will add more routes here in the next steps
Route::apiResource('products', ProductController::class)->only(['index']);
