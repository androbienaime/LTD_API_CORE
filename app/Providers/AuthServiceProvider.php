<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Core\Account;
use App\Models\Core\Carrier;
use App\Policies\Core\AccountPolicy;
use App\Policies\Core\CarrierPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Account::class => AccountPolicy::class,
        Carrier::class => CarrierPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
