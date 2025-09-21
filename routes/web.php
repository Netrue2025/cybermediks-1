<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

/** Patient Auth (guest) */
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class,'showLogin'])->name('login.show');
    Route::post('/login',   [AuthController::class,'login'])->name('login');

    Route::get('/register', [AuthController::class,'showRegister'])->name('register.show');
    Route::post('/register',[AuthController::class,'register'])->name('register');

    Route::get('/forgot',   [PasswordController::class,'showForgot'])->name('forgot.show');
    Route::post('/forgot',  [PasswordController::class,'sendResetCode'])->middleware('throttle:5,1')->name('forgot.send');

    Route::get('/reset',    [PasswordController::class,'showReset'])->name('reset.show');
    Route::post('/reset',   [PasswordController::class,'reset'])->name('reset');
});

/** Patient Auth (authed) */
Route::middleware('auth')->group(function () {
    Route::post('/logout',  [AuthController::class,'logout'])->name('logout');

    // Email verification
    Route::get('/verify',   [VerificationController::class,'showVerify'])->name('verify.show');
    Route::post('/verify/send', [VerificationController::class,'sendVerifyCode'])->middleware('throttle:5,1')->name('verify.send');
    Route::post('/verify',       [VerificationController::class,'verify'])->name('verify');

});

/** Patient Dashboard (authed + verified) */
Route::middleware(['auth', 'verified', 'patient'])->group(function () {
    Route::view('/patient/dashboard', 'patient.dashboard')->name('patient.dashboard');
});