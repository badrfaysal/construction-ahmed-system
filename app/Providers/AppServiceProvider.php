<?php

namespace App\Providers;

use App\Models\Installment;
use App\Models\Material;
use App\Models\MaterialReturn;
use App\Models\ProjectBand;
use App\Models\Settings;
use App\Models\Transaction;
use App\Observers\InstallmentObserver;
use App\Observers\MaterialObserver;
use App\Observers\MaterialReturnObserver;
use App\Observers\ProjectBandObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\Facades\View;
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
        // سجل الحركات (transactions) is never entered by hand — these observers
        // keep it in sync automatically whenever materials, payments, or wages happen
        Material::observe(MaterialObserver::class);
        MaterialReturn::observe(MaterialReturnObserver::class);
        Installment::observe(InstallmentObserver::class);
        ProjectBand::observe(ProjectBandObserver::class);

        // محفظة المقاولات (the dedicated wallet in the external accounts
        // table) is debited/credited in lockstep with سجل الحركات itself —
        // every transaction row created/changed/removed above flows through here.
        Transaction::observe(TransactionObserver::class);

        // System-wide settings (default supervision %, company info, WhatsApp
        // country code) available in every view as $settings. Skipped in
        // console so artisan commands never touch a table that might not
        // exist yet (e.g. before migrations have run).
        if (! $this->app->runningInConsole()) {
            View::share('settings', Settings::current());
        }
    }
}
