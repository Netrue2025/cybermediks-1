<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Doctor\DoctorCredentialController;
use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorLocationController;
use App\Http\Controllers\Doctor\DoctorMessengerController;
use App\Http\Controllers\Doctor\DoctorPatientController;
use App\Http\Controllers\Doctor\DoctorPrescriptionController;
use App\Http\Controllers\Doctor\DoctorProfileController;
use App\Http\Controllers\Doctor\DoctorScheduleController;
use App\Http\Controllers\Doctor\DoctorWalletController;
use App\Http\Controllers\Patient\DoctorBrowseController;
use App\Http\Controllers\Patient\PatientAppointmentController;
use App\Http\Controllers\Patient\PatientDashboardController;
use App\Http\Controllers\Patient\PatientLocationController;
use App\Http\Controllers\Patient\PatientMessageController;
use App\Http\Controllers\Patient\PatientProfileController;
use App\Http\Controllers\Patient\PrescriptionController;
use App\Http\Controllers\Patient\WalletController;
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
Route::prefix('patient')->name('patient.')->middleware(['auth', 'verified', 'patient'])->group(function () {

    Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/store', fn() => view('patient.store'))->name('store');
    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/appointments', [PatientAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/pharmacies', fn() => view('patient.pharmacies'))->name('pharmacies');

    // DOCTORS
    Route::get('/doctors', [DoctorBrowseController::class, 'index'])->name('doctors.index');

    // WALLETS
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/add-funds', [WalletController::class, 'addFunds'])->name('wallet.add');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');


    Route::post('/location', [PatientLocationController::class, 'store'])
        ->name('location.update');
    Route::get('/profile', [PatientProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [PatientProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [PatientProfileController::class, 'updatePassword'])->name('profile.password');

    // Messages
    Route::get('/messages', [PatientMessageController::class, 'index'])->name('messages');
    Route::get('/messages/conversations', [PatientMessageController::class, 'conversations'])->name('messages.conversations'); // AJAX
    Route::get('/messages/{conversation}', [PatientMessageController::class, 'show'])->name('messages.show'); // AJAX
    Route::post('/messages/{conversation}', [PatientMessageController::class, 'send'])->name('messages.send'); // AJAX
    Route::post('/messages/start', [PatientMessageController::class, 'start'])->name('messages.start'); // AJAX (start convo with a doctor)

    // Appointments
    Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store'); // AJAX
});


/** Doctor Dashboard (authed + verified) */
Route::prefix('doctor')->name('doctor.')->middleware(['auth', 'verified', 'doctor'])->group(function () {
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');


    // PRESCRIPTIONS
    Route::get('/prescriptions/create', [DoctorPrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions', [DoctorPrescriptionController::class, 'store'])->name('prescriptions.store');


    // SCHEDULE
    Route::get('/schedule', [DoctorScheduleController::class, 'index'])->name('schedule');
    Route::post('/schedule', [DoctorScheduleController::class, 'store'])->name('schedule.store');

    // PATIENTS
    Route::get('/patients', [DoctorPatientController::class, 'index'])->name('patients');
    Route::get('/patient/{patient}/history', [DoctorPatientController::class, 'show'])->name('patient.history');

    // WALLETS
    Route::get('/wallet', [DoctorWalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/add-funds', [DoctorWalletController::class, 'addFunds'])->name('wallet.add');
    Route::post('/wallet/withdraw', [DoctorWalletController::class, 'withdraw'])->name('wallet.withdraw');

    // MESSENGERS
    Route::get('/messenger', [DoctorMessengerController::class, 'index'])->name('messenger'); // list + optional open
    Route::get('/messenger/{conversation}', [DoctorMessengerController::class, 'show'])->name('messenger.show'); // messages
    Route::post('/messenger/{conversation}/messages', [DoctorMessengerController::class, 'send'])->name('messenger.send');

    Route::get('/credentials', [DoctorCredentialController::class, 'index'])->name('credentials.index'); // optional page
    Route::post('/credentials', [DoctorCredentialController::class, 'store'])->name('credentials.store'); // upload
    Route::delete('/credentials/{credential}', [DoctorCredentialController::class, 'destroy'])->name('credentials.destroy'); // delete
    Route::get('/credentials/{credential}/download', [DoctorCredentialController::class, 'download'])->name('credentials.download'); // download/view
    Route::get('/credentials/list/fragment', [DoctorCredentialController::class, 'listFragment'])->name('credentials.fragment'); // return partial HTML


    // PROFILE
    Route::get('/profile', [DoctorProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [DoctorProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [DoctorProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/availability', [DoctorProfileController::class, 'availability'])->name('profile.availability');
    Route::post('/profile/quick', [DoctorProfileController::class, 'quickUpdate'])->name('profile.quick');            


    
    Route::get('/queue', fn() => view('doctor.queue'))->name('queue');


    Route::get('/consultations', fn() => view('doctor.consultations'))->name('consultations');
    
    Route::post('/location', [DoctorLocationController::class, 'store'])
        ->name('location.update');
});
