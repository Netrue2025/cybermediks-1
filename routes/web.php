<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Doctor\DoctorProfileController;
use App\Http\Controllers\Patient\PatientLocationController;
use App\Http\Controllers\Patient\PatientProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

/** Patient Auth (guest) */
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login.show');
    Route::post('/login',   [AuthController::class, 'login'])->name('login');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    Route::get('/forgot',   [PasswordController::class, 'showForgot'])->name('forgot.show');
    Route::post('/forgot',  [PasswordController::class, 'sendResetCode'])->middleware('throttle:5,1')->name('forgot.send');

    Route::get('/reset',    [PasswordController::class, 'showReset'])->name('reset.show');
    Route::post('/reset',   [PasswordController::class, 'reset'])->name('reset');
});

/** Patient Auth (authed) */
Route::middleware('auth')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

    // Email verification
    Route::get('/verify',   [VerificationController::class, 'showVerify'])->name('verify.show');
    Route::post('/verify/send', [VerificationController::class, 'sendVerifyCode'])->middleware('throttle:5,1')->name('verify.send');
    Route::post('/verify',       [VerificationController::class, 'verify'])->name('verify');
});

/** Patient Dashboard (authed + verified) */
Route::middleware(['auth', 'verified', 'patient'])->group(function () {

    Route::view('/patient/dashboard', 'patient.dashboard')->name('patient.dashboard');
    Route::get('/patient/store', fn() => view('patient.store'))->name('patient.store');
    Route::get('/patient/prescriptions', fn() => view('patient.prescriptions'))->name('patient.prescriptions');
    Route::get('/patient/appointments', fn() => view('patient.appointments'))->name('patient.appointments');
    Route::get('/patient/wallet', fn() => view('patient.wallet'))->name('patient.wallet');
    Route::get('/patient/pharmacies', fn() => view('patient.pharmacies'))->name('patient.pharmacies');


    Route::post('/patient/location', [PatientLocationController::class, 'store'])
        ->name('patient.location.save');
    Route::get('/patient/profile', [PatientProfileController::class, 'show'])->name('patient.profile');
    Route::post('/patient/profile', [PatientProfileController::class, 'update'])->name('patient.profile.update');
    Route::post('/patient/profile/password', [PatientProfileController::class, 'updatePassword'])->name('patient.profile.password');
});


/** Doctor Dashboard (authed + verified) */
Route::prefix('doctor')->name('doctor.')->middleware(['auth', 'verified', 'doctor'])->group(function () {
    Route::view('/dashboard', 'doctor.dashboard')->name('dashboard');

    Route::get('/patients', fn() => view('doctor.patients'))->name('patients');
    Route::get('/messenger', fn() => view('doctor.messenger'))->name('messenger');
    Route::get('/prescriptions/create', fn() => view('doctor.prescriptions.create'))->name('prescriptions.create');
    Route::get('/prescriptions', fn() => view('doctor.prescriptions'))->name('prescriptions');
    Route::get('/schedule', fn() => view('doctor.schedule'))->name('schedule');
    Route::get('/credentials', fn() => view('doctor.credentials'))->name('credentials');
    Route::get('/queue', fn() => view('doctor.queue'))->name('queue');


    Route::get('/wallet', fn() => view('doctor.wallet'))->name('wallet');
     Route::get('/consultations', fn() => view('doctor.consultations'))->name('consultations');
    Route::get('/profile', [DoctorProfileController::class, 'show'])->name('profile');

    Route::post('/profile', [DoctorProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [DoctorProfileController::class, 'updatePassword'])->name('profile.password');
});
