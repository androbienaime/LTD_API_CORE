<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AccountAuthenticate extends Middleware
{

    protected function authenticate($request, array $guards)
    {

        if ($this->auth->guard('account')->check()) {
            return $this->auth->shouldUse('account');
        }

        if ($this->auth->guard('account-service')->check()) {
            return $this->auth->shouldUse('account-service');
        }

        $this->unauthenticated($request, ['account', 'account-service']);
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('filament.shop.auth.login');
        }
    }
}
