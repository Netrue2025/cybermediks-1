<?php

namespace App\Providers;

use App\Models\Prescription;
use App\Observers\PrescriptionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Prescription::observe(PrescriptionObserver::class);
    }
}
