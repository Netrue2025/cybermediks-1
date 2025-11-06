<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->web(append: [
            \App\Http\Middleware\DetectCountryFromIp::class,
            \App\Http\Middleware\TouchLastSeen::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'mustVerify' => \App\Http\Middleware\MustVerify::class,
            // 'patient' => \App\Http\Middleware\PatientMiddleware::class,
            // 'doctor'  => \App\Http\Middleware\DoctorMiddleware::class,
            // 'dispatcher' => \App\Http\Middleware\DispatcherMiddleware::class,
            // 'pharmacy' => \App\Http\Middleware\PharmacyMiddleware::class,
            // 'transport' => \App\Http\Middleware\TransportMiddleware::class,
            // 'health' => \App\Http\Middleware\HealthMiddleware::class,
            // 'labtech' => \App\Http\Middleware\LabTechMiddleware::class,
            // 'admin'   => \App\Http\Middleware\AdminMiddleware::class,
        ]);


    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
