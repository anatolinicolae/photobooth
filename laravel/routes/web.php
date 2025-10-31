<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Middleware\AuthenticateWithApiToken;

Route::get('/', function () {
    return view('gallery');
});

// API Token Management Routes (unprotected - for initial setup)
Route::prefix('api/tokens')->group(function () {
    Route::post('/', [ApiTokenController::class, 'create']);
    Route::get('/user/{userId}', [ApiTokenController::class, 'index']);
    Route::delete('/user/{userId}/{tokenId}', [ApiTokenController::class, 'revoke']);
    Route::delete('/user/{userId}', [ApiTokenController::class, 'revokeAll']);
});

// Protected API routes for image management (require API token)
Route::middleware([AuthenticateWithApiToken::class])->prefix('api')->group(function () {
    Route::post('/images', [ImageController::class, 'upload'])->middleware(AuthenticateWithApiToken::class . ':upload');
    Route::delete('/images/{id}', [ImageController::class, 'delete'])->middleware(AuthenticateWithApiToken::class . ':delete');
});

// Public API routes (no authentication required)
Route::get('/api/images', [ImageController::class, 'list']);

// Server-Sent Events endpoint
Route::get('/api/events', [ImageController::class, 'events']);
