<?php

namespace App\Providers;

use App\Models\Prescription;
use App\Observers\PrescriptionObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Support\CurrencyContext::class, fn($app) => new \App\Support\CurrencyContext());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Prescription::observe(PrescriptionObserver::class);

        Blade::directive('money', function ($expr) {
            // usage: @money($amountNgn) or @money($amountNgn, 'USD')
            return "<?php echo money_display($expr); ?>";
        });
    }
}
