<?php

namespace App\Observers;

use App\Models\Core\Roles\Role;

class RoleObserver
{
    public function creating(Role $role)
    {

        if (!$role->tenant_id) {
            $role->tenant_id = auth()->guard('web')->check()
                ? auth()->guard('web')->user()->id
                : auth()->guard('account')->user()?->shopActive->first()->id;
        }
    }
}
