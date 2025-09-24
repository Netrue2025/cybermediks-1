<?php

use App\Http\Controllers\FlutterwaveWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/flutterwave/webhook', [FlutterwaveWebhookController::class, 'handle'])
    ->name('flutterwave.webhook');
