<?php

namespace App\Providers;

use App\Models\Core\Customer;
use App\Models\Core\Roles\Role;
use App\Observers\RoleObserver;
use App\Observers\CustomerObserver;
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

    public static function boot() : void
    {
        // Customer::observe(CustomerObserver::class);
        Role::observe(RoleObserver::class);
    }
}
