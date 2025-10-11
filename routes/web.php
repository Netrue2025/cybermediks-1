<?php

use App\Http\Controllers\Admin\AdminAppointmentsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDispatchersController;
use App\Http\Controllers\Admin\AdminDisputeController;
use App\Http\Controllers\Admin\AdminDoctorsController;
use App\Http\Controllers\Admin\AdminPharmaciesController;
use App\Http\Controllers\Admin\AdminPrescriptionsController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminsController;
use App\Http\Controllers\Admin\AdminSpecialtiesController;
use App\Http\Controllers\Admin\AdminTransactionsController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminWithdrawalRequestController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Dispatcher\DispatcherDashboardController;
use App\Http\Controllers\Dispatcher\DispatcherOrderController;
use App\Http\Controllers\Dispatcher\DispatcherPrescriptionController;
use App\Http\Controllers\Doctor\DoctorConversationQuickController;
use App\Http\Controllers\Doctor\DoctorCredentialController;
use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorLocationController;
use App\Http\Controllers\Doctor\DoctorMessengerController;
use App\Http\Controllers\Doctor\DoctorPatientController;
use App\Http\Controllers\Doctor\DoctorPrescriptionController;
use App\Http\Controllers\Doctor\DoctorProfileController;
use App\Http\Controllers\Doctor\DoctorQueueController;
use App\Http\Controllers\Doctor\DoctorScheduleController;
use App\Http\Controllers\Doctor\DoctorWalletController;
use App\Http\Controllers\Health\HealthDashboardController;
use App\Http\Controllers\Labtech\LabtechDashboardController;
use App\Http\Controllers\Labtech\LabtechLabworkController;
use App\Http\Controllers\Patient\DoctorBrowseController;
use App\Http\Controllers\Patient\PatientAppointmentController;
use App\Http\Controllers\Patient\PatientDashboardController;
use App\Http\Controllers\Patient\PatientLabworkController;
use App\Http\Controllers\Patient\PatientLocationController;
use App\Http\Controllers\Patient\PatientMessageController;
use App\Http\Controllers\Patient\PatientProfileController;
use App\Http\Controllers\Patient\PrescriptionController;
use App\Http\Controllers\Patient\WalletController;
use App\Http\Controllers\Pharmacy\PharmacyDashboardController;
use App\Http\Controllers\Pharmacy\PharmacyDispensedController;
use App\Http\Controllers\Pharmacy\PharmacyInventoryController;
use App\Http\Controllers\Pharmacy\PharmacyOrderController;
use App\Http\Controllers\Pharmacy\PharmacyPrescriptionController;
use App\Http\Controllers\Pharmacy\PharmacyProfileController;
use App\Http\Controllers\Pharmacy\PharmacyReportsController;
use App\Http\Controllers\Pharmacy\PharmacySettingsController;
use App\Http\Controllers\Pharmacy\PharmacyWalletController;
use App\Http\Controllers\Transport\TransportDashboardController;
use App\Http\Controllers\WithdrawalRequestController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');


Route::get('/login',    [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login',   [AuthController::class, 'login'])->name('login');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/forgot',   [PasswordController::class, 'showForgot'])->name('forgot.show');
Route::post('/forgot',  [PasswordController::class, 'sendResetCode'])->middleware('throttle:5,1')->name('forgot.send');

Route::get('/reset',    [PasswordController::class, 'showReset'])->name('reset.show');
Route::post('/reset',   [PasswordController::class, 'reset'])->name('reset');

/** Auth (authed) */
Route::middleware('auth')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');

    // Email verification
    Route::get('/verify',   [VerificationController::class, 'showVerify'])->name('verify.show');
    Route::post('/verify/send', [VerificationController::class, 'sendVerifyCode'])->middleware('throttle:5,1')->name('verify.send');
    Route::post('/verify',       [VerificationController::class, 'verify'])->name('verify');

    Route::post('/wallet/withdraw', [WithdrawalRequestController::class, 'requestWithdraw'])->name('wallet.withdraw');
});

// PATIENT ROUTES

Route::prefix('patient')->name('patient.')->middleware(['auth', 'verified', 'patient'])->group(function () {

    Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/store', [PatientDashboardController::class, 'products'])->name('store');
    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/{rx}/pharmacies', [PrescriptionController::class, 'list'])->name('prescriptions.pharmacies');
    Route::post('/prescriptions/{rx}/assign-pharmacy', [PrescriptionController::class, 'assign'])->name('prescriptions.assignPharmacy');
    Route::post('/prescriptions/{rx}/confirm-price', [PrescriptionController::class, 'confirm'])->name('prescriptions.confirmPrice');
    Route::post('/prescriptions/{rx}/confirm-delivery-fee', [PrescriptionController::class, 'confirmDeliveryFee'])->name('confirmDeliveryFee');
    Route::post('/orders/{order}/confirm-delivery-fee', [PrescriptionController::class, 'confirmDeliveryFee'])->name('orders.confirmDeliveryFee');

    Route::get('/pharmacies', fn() => view('patient.pharmacies'))->name('pharmacies');

    // DOCTORS
    Route::get('/doctors', [DoctorBrowseController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/{doctor}', [DoctorBrowseController::class, 'show'])->name('doctors.show'); // returns JSON

    // WALLETS
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/add-funds', [WalletController::class, 'addFunds'])->name('wallet.add');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');


    Route::post('/location', [PatientLocationController::class, 'store'])->name('location.update');
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
    Route::get('/appointments', [PatientAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store'); // AJAX
    Route::post('/appointments/close/{id}', [PatientAppointmentController::class, 'close'])->name('appointments.close'); // AJAX
    Route::post('/appointments/{appointment}/dispute', [PatientAppointmentController::class, 'storeDispute'])->name('appointments.dispute');


    // LABWORKS
    Route::get('/labworks',                 [PatientLabworkController::class, 'index'])->name('labworks.index');
    Route::get('/labworks/create',          [PatientLabworkController::class, 'create'])->name('labworks.create');
    Route::post('/labworks',                [PatientLabworkController::class, 'store'])->name('labworks.store');
    Route::get('/labworks/{lab}',           [PatientLabworkController::class, 'show'])->name('labworks.show');
    Route::post('/labworks/{lab}/cancel',   [PatientLabworkController::class, 'cancel'])->name('labworks.cancel');

    // provider search + assign (optional)
    Route::get('/labworks/providers/search',       [PatientLabworkController::class, 'providers'])->name('labworks.providers');
    Route::post('/labworks/{lab}/assign-provider', [PatientLabworkController::class, 'assignProvider'])->name('labworks.assignProvider');

    // download results (authorized)
    Route::get('/labworks/{lab}/download',  [PatientLabworkController::class, 'downloadResults'])->name('labworks.download');



    Route::post('/wallet/pay', [WalletController::class, 'startFlutterwave'])->name('wallet.pay');           // create payment link
    Route::get('/wallet/callback', [WalletController::class, 'flutterwaveCallback'])->name('wallet.callback');   // user returns here
});


// DOCTOR ROUTES
Route::prefix('doctor')->name('doctor.')->middleware(['auth', 'verified', 'doctor'])->group(function () {
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');


    // PRESCRIPTIONS
    Route::get('/prescriptions', [DoctorPrescriptionController::class, 'index'])->name('prescriptions.index'); // if you don’t have it yet
    Route::get('/prescriptions/get/{rx}', [DoctorPrescriptionController::class, 'show'])->name('prescriptions.show');
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

    Route::post('/conversations/{conversation}/accept', [DoctorConversationQuickController::class, 'accept'])->name('conversations.accept');
    Route::post('/conversations/{conversation}/reject', [DoctorConversationQuickController::class, 'reject'])->name('conversations.reject');
    Route::post('/conversations/{conversation}/reopen', [DoctorConversationQuickController::class, 'reopen'])->name('conversations.reopen');
    Route::post('/conversations/{conversation}/close',  [DoctorConversationQuickController::class, 'close'])->name('conversations.close');

    Route::get('/queue', fn() => view('doctor.queue'))->name('queue');
    Route::post('/queue/{appointment}/accept', [DoctorQueueController::class, 'accept'])->name('queue.accept');
    Route::post('/queue/{appointment}/completed', [DoctorQueueController::class, 'completed'])->name('queue.completed');
    Route::post('/queue/{appointment}/reject', [DoctorQueueController::class, 'reject'])->name('queue.reject');
    Route::post('/queue/{appointment}/meeting-link', [DoctorQueueController::class, 'saveMeetingLink'])->name('queue.saveLink');


    Route::get('/consultations', fn() => view('doctor.consultations'))->name('consultations');

    Route::post('/location', [DoctorLocationController::class, 'store'])
        ->name('location.update');
});


// PHARMACY ROUTES

Route::prefix('pharmacy')->name('pharmacy.')->middleware(['auth', 'verified', 'pharmacy'])->group(function () {

    Route::get('/dashboard', [PharmacyDashboardController::class, 'index'])->name('dashboard');

    // Prescriptions

    Route::get('/prescriptions', [PharmacyPrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/{rx}', [PharmacyPrescriptionController::class, 'show'])->name('prescriptions.show');

    Route::post('/prescriptions/{rx}/status', [PharmacyPrescriptionController::class, 'updateStatus'])->name('prescriptions.status');
    Route::post('/prescriptions/{rx}/amount', [PharmacyPrescriptionController::class, 'updateAmount'])->name('prescriptions.amount');

    Route::post('/prescriptions/{rx}/claim', [PharmacyPrescriptionController::class, 'claim'])->name('prescriptions.claim');

    // actions
    Route::post('/prescriptions/{rx}/ready',  [PharmacyDashboardController::class, 'markReady'])->name('rx.ready');
    Route::post('/prescriptions/{rx}/picked', [PharmacyDashboardController::class, 'markPicked'])->name('rx.picked');


    // Inventory
    Route::get('/inventory', [PharmacyInventoryController::class, 'show'])->name('inventory.show');
    Route::post('/inventory', [PharmacyInventoryController::class, 'upload'])->name('inventory.upload');
    Route::get('/inventory/download', [PharmacyInventoryController::class, 'download'])->name('inventory.download');

    // Reports
    Route::get('/reports', [PharmacyReportsController::class, 'index'])->name('reports.index');

    // Settings
    Route::get('/settings', [PharmacySettingsController::class, 'show'])->name('settings.show');
    Route::post('/settings', [PharmacySettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/license', [PharmacySettingsController::class, 'updateLicense'])->name('settings.license.update');

    // PROFILE
    Route::get('/profile', [PharmacySettingsController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [PharmacySettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [PharmacySettingsController::class, 'updatePassword'])->name('profile.password');

    // WALLETS
    Route::get('/wallet', [PharmacyWalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/withdraw', [PharmacyWalletController::class, 'withdraw'])->name('wallet.withdraw');

    Route::get('/dispensed', [PharmacyDispensedController::class, 'index'])->name('dispensed.index');
    Route::post('/dispensed/{rx}/undo', [PharmacyDispensedController::class, 'undo'])->name('dispensed.undo'); // set back to 'ready'
    // optional: receipt
    Route::get('/dispensed/{rx}/receipt', [PharmacyDispensedController::class, 'receipt'])->name('dispensed.receipt');


    Route::get('orders',                [PharmacyOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}',        [PharmacyOrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/status', [PharmacyOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/picked', [PharmacyOrderController::class, 'markPicked'])->name('orders.markPicked');
});

// DISPATCHER ROUTES

Route::prefix('dispatcher')->name('dispatcher.')->middleware(['auth', 'verified', 'dispatcher'])->group(function () {

    Route::get('/dashboard', [DispatcherDashboardController::class, 'index'])->name('dashboard');

    // Prescriptions
    Route::post('/prescriptions/{rx}/accept', [DispatcherPrescriptionController::class, 'accept'])->name('prescriptions.accept');
    Route::post('/prescriptions/{rx}/set-delivery-fee', [DispatcherPrescriptionController::class, 'setDeliveryFee'])->name('setDeliveryFee');

    Route::post('/prescriptions/{rx}/deliver', [DispatcherPrescriptionController::class, 'markDelivered'])->name('prescriptions.deliver');
    Route::get('/deliveries', [DispatcherDashboardController::class, 'getDeliveries'])->name('deliveries.index');

    Route::post('/orders/{order}/accept',  [DispatcherOrderController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/fee',     [DispatcherOrderController::class, 'setDeliveryFee'])->name('orders.setDeliveryFee');
    Route::post('/orders/{order}/deliver', [DispatcherOrderController::class, 'markDelivered'])->name('orders.deliver');

    // PROFILE
    Route::get('/profile', [DispatcherDashboardController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [DispatcherDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [DispatcherDashboardController::class, 'updatePassword'])->name('profile.password');

    // WALLETS
    Route::get('/wallet', [DispatcherDashboardController::class, 'walletIndex'])->name('wallet.index');
    Route::post('/wallet/withdraw', [DispatcherDashboardController::class, 'withdraw'])->name('wallet.withdraw');
});


// ADMIN ROUTES

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [AdminUsersController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle', [AdminUsersController::class, 'toggleActive'])->name('users.toggle');

    Route::get('/doctors', [AdminDoctorsController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/{doctor}/credentials', [AdminDoctorsController::class, 'credentials'])->name('doctors.credentials');
    Route::post('/doctors/{id}/availability', [AdminDoctorsController::class, 'availability'])->name('doctors.availability');
    Route::post('/doctors/{id}/approve-credential', [AdminDoctorsController::class, 'approveCredential'])->name('doctors.approveCredential');

    Route::get('/pharmacies', [AdminPharmaciesController::class, 'index'])->name('pharmacies.index');
    Route::get('/pharmacies/{pharmacy}/profile', [AdminPharmaciesController::class, 'profile'])->name('pharmacies.profile');
    Route::post('/pharmacies/{pharmacy}/toggle24', [AdminPharmaciesController::class, 'toggle24'])->name('pharmacies.toggle24');
    Route::post('/pharmacies/{pharmacy}/radius', [AdminPharmaciesController::class, 'updateRadius'])->name('pharmacies.updateRadius');

    Route::get('/prescriptions', [AdminPrescriptionsController::class, 'index'])->name('prescriptions.index');
    Route::post('/prescriptions/{rx}/reassign-pharmacy', [AdminPrescriptionsController::class, 'reassignPharmacy'])->name('prescriptions.reassignPharmacy');
    Route::post('/prescriptions/{rx}/assign-dispatcher', [AdminPrescriptionsController::class, 'assignDispatcher'])->name('prescriptions.assignDispatcher');

    Route::get('/appointments', [AdminAppointmentsController::class, 'index'])->name('appointments.index');

    Route::get('/transactions', [AdminTransactionsController::class, 'index'])->name('transactions.index');

    Route::get('/dispatchers', [AdminDispatchersController::class, 'index'])->name('dispatchers.index');
    Route::get('/dispatchers/{dispatcher}/profile', [AdminDispatchersController::class, 'profile'])->name('dispatchers.profile');

    Route::get('/specialties', [AdminSpecialtiesController::class, 'index'])->name('specialties.index');
    Route::resource('specialties', AdminSpecialtiesController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/products',        [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/products',       [AdminProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}',   [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');


    // Admin management
    Route::get('/admins', [AdminsController::class, 'index'])->name('admins.index');
    Route::post('/admins', [AdminsController::class, 'store'])->name('admins.store');
    Route::delete('/admins/{id}', [AdminsController::class, 'destroy'])->name('admins.destroy');

    // WITHDRAWAL REQUEST
    Route::get('/withdrawals', [AdminWithdrawalRequestController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{wd}/approve', [AdminWithdrawalRequestController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{wd}/payout',  [AdminWithdrawalRequestController::class, 'payout'])->name('withdrawals.payout');
    Route::post('/withdrawals/{wd}/reject',  [AdminWithdrawalRequestController::class, 'reject'])->name('withdrawals.reject');


    // ADMIN DISPUTES
    Route::get('/disputes',                [AdminDisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}',      [AdminDisputeController::class, 'show'])->name('disputes.show'); // optional
    Route::post('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');
});

// HEALTH ROUTES

Route::middleware(['auth', 'verified', 'health'])->prefix('health')->name('health.')->group(function () {
    Route::get('/', [HealthDashboardController::class, 'index'])->name('dashboard');

    Route::get('/doctors', [HealthDashboardController::class, 'doctorIndex'])->name('doctors.index');
    Route::get('/doctors/{doctor}/credentials', [HealthDashboardController::class, 'credentials'])->name('doctors.credentials');
    Route::post('/doctors/{id}/approve-credential', [HealthDashboardController::class, 'approveCredential'])->name('doctors.approveCredential');
    Route::post('/doctors/{id}/reject-credential', [HealthDashboardController::class, 'rejectCredential'])->name('doctors.rejectCredential');

    Route::get('/profile', [HealthDashboardController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [HealthDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [HealthDashboardController::class, 'updatePassword'])->name('profile.password');
});


// TRANSPORT ROUTES

Route::middleware(['auth', 'verified', 'transport'])->prefix('transport')->name('transport.')->group(function () {
    Route::get('/', [TransportDashboardController::class, 'index'])->name('dashboard');

    Route::get('/pharmacies', [TransportDashboardController::class, 'pharmacyIndex'])->name('pharmacies.index');
    Route::post('/pharmacies/{pharmacy}/approve', [TransportDashboardController::class, 'approveLicense'])->name('pharmacies.approve');
    Route::post('/pharmacies/{pharmacy}/reject', [TransportDashboardController::class, 'rejectLicense'])->name('pharmacies.reject');
    Route::get('/pharmacies/{pharmacy}/profile', [TransportDashboardController::class, 'profile'])->name('pharmacies.profile');

    Route::get('/profile', [TransportDashboardController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [TransportDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [TransportDashboardController::class, 'updatePassword'])->name('profile.password');
});


// LABTECH ROUTES

Route::prefix('labtech')->name('labtech.')->middleware(['auth', 'verified', 'labtech'])->group(function () {

    Route::get('/dashboard', [LabtechDashboardController::class, 'index'])->name('dashboard');

    Route::get('/labworks',                [LabtechLabworkController::class, 'index'])->name('labworks.index');
    Route::get('/labworks/{lab}',          [LabtechLabworkController::class, 'show'])->name('labworks.show');

    Route::post('/labworks/{lab}/accept',  [LabtechLabworkController::class, 'accept'])->name('labworks.accept');
    Route::post('/labworks/{lab}/reject',  [LabtechLabworkController::class, 'reject'])->name('labworks.reject');

    Route::post('/labworks/{lab}/schedule',     [LabtechLabworkController::class, 'schedule'])->name('labworks.schedule');
    Route::post('/labworks/{lab}/price',        [LabtechLabworkController::class, 'setPrice'])->name('labworks.price');
    Route::post('/labworks/{lab}/start',        [LabtechLabworkController::class, 'start'])->name('labworks.start');

    // upload results BEFORE completion
    Route::post('/labworks/{lab}/upload-results', [LabtechLabworkController::class, 'uploadResults'])->name('labworks.uploadResults');
    Route::post('/labworks/{lab}/complete',       [LabtechLabworkController::class, 'complete'])->name('labworks.complete');

    // optional: delete/replace results
    Route::post('/labworks/{lab}/clear-results',  [LabtechLabworkController::class, 'clearResults'])->name('labworks.clearResults');


    // WALLETS
    Route::get('/wallet', [LabtechDashboardController::class, 'walletIndex'])->name('wallet.index');
    Route::post('/wallet/withdraw', [LabtechDashboardController::class, 'withdraw'])->name('wallet.withdraw');

    // PROFILE
    Route::get('/profile', [LabtechDashboardController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [LabtechDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [LabtechDashboardController::class, 'updatePassword'])->name('profile.password');
});
