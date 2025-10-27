<?php

namespace App\Providers;

use App\Models\LaporanHarianJaga;
use App\Models\ReportType;
use App\Models\Report;
use App\Policies\LaporanHarianJagaPolicy;
use App\Policies\ReportTypePolicy;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     *
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        LaporanHarianJaga::class => LaporanHarianJagaPolicy::class,
        ReportType::class => ReportTypePolicy::class,
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
