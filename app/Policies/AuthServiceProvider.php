<?php

namespace App\Providers;

use App\Models\LaporanHarianJaga;
use App\Models\ReportType;
use App\Models\Report;
use App\Models\User;
use App\Policies\LaporanHarianJagaPolicy;
use App\Policies\ReportTypePolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use App\Models\Attendance;
use App\Policies\AttendancePolicy;
use App\Models\LeaveRequest;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;
use App\Listeners\UpdateLastLoginAt;

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
        User::class => UserPolicy::class,
        Attendance::class => AttendancePolicy::class,
        LeaveRequest::class => LeaveRequestPolicy::class,
    ];

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [
            UpdateLastLoginAt::class,
        ],
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
