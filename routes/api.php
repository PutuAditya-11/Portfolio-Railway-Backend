<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'Portfolio Blog API',
        'version' => '1.0.0'
    ]);
});

// API v1 routes
Route::prefix('v1')->group(function () {
    // Blog Posts
    Route::get('posts', [BlogPostController::class, 'index']);
    Route::get('posts/{identifier}', [BlogPostController::class, 'show']);
    Route::get('posts/{identifier}/related', [BlogPostController::class, 'getRelated']);
    
    // Specific slug endpoint
    Route::get('posts/slug/{slug}', [BlogPostController::class, 'getBySlug']);
    
    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);

    Route::get('categories/slug/{slug}', [CategoryController::class, 'show']);
    Route::get('categories/{slug}/posts', [CategoryController::class, 'posts']);
    
    // Tags
    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{id}', [TagController::class, 'show']);

    Route::get('tags/slug/{slug}', [TagController::class, 'show']);
    Route::get('tags/{slug}/posts', [TagController::class, 'posts']);
});

// Additional user route (Laravel default)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');