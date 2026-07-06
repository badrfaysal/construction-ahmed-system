<?php

namespace App\Providers;

use App\Models\InstallmentContract;
use App\Models\InstallmentPayment;
use App\Models\Material;
use App\Models\MaterialReturn;
use App\Models\ProjectBand;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\WorkerPayment;
use App\Observers\InstallmentContractObserver;
use App\Observers\InstallmentPaymentObserver;
use App\Observers\MaterialObserver;
use App\Observers\MaterialReturnObserver;
use App\Observers\ProjectBandObserver;
use App\Observers\TransactionAuditObserver;
use App\Observers\TransactionObserver;
use App\Observers\WorkerPaymentObserver;
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
        ProjectBand::observe(ProjectBandObserver::class);
        WorkerPayment::observe(WorkerPaymentObserver::class);

        // نظام العقود والأقساط الجديد (بديل الأقساط القديمة المربوطة بالمشاريع)
        InstallmentContract::observe(InstallmentContractObserver::class);
        InstallmentPayment::observe(InstallmentPaymentObserver::class);

        // محفظة المقاولات (the dedicated wallet in the external accounts
        // table) is debited/credited in lockstep with سجل الحركات itself —
        // every transaction row created/changed/removed above flows through here.
        Transaction::observe(TransactionObserver::class);

        // سجل تدقيق ثابت (append-only) لكل حركة — منفصل عن TransactionObserver
        // فوق عشان يفضل يسجّل حتى لو المحفظة/المرآة رفضت العملية لاحقًا لسبب آخر
        Transaction::observe(TransactionAuditObserver::class);

        // System-wide settings (default supervision %, company info, WhatsApp
        // country code) available in every view as $settings. Skipped in
        // console so artisan commands never touch a table that might not
        // exist yet (e.g. before migrations have run).
        if (! $this->app->runningInConsole()) {
            View::share('settings', Settings::current());
        }
    }
}
