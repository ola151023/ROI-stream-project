<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Observers\ActivityLogObserver;
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
       // ActivityLog::observe(ActivityLogObserver::class);
    }
}
