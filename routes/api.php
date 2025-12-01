<?php

use App\Http\Controllers\FlutterwaveController;
use App\Http\Controllers\FlutterwaveWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/flutterwave/webhook', [FlutterwaveWebhookController::class, 'handle'])
    ->name('flutterwave.webhook');

// Flutterwave API endpoints
Route::get('/flutterwave/banks', [FlutterwaveController::class, 'getBanks']);
Route::post('/flutterwave/verify-account', [FlutterwaveController::class, 'verifyAccount']);
