<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::get('/', function () {
    return view('gallery');
});

// API routes for image management
Route::post('/api/images', [ImageController::class, 'upload']);
Route::get('/api/images', [ImageController::class, 'list']);
Route::delete('/api/images/{id}', [ImageController::class, 'delete']);

// Server-Sent Events endpoint
Route::get('/api/events', [ImageController::class, 'events']);
