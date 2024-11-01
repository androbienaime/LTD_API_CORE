<?php

namespace App\Policies\Core\Roles;

use App\Models\User;
use App\Models\Core\Account;
use App\Models\Core\Roles\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;
    

    /**
     * Determine whether the account can view any models.
     */
    public function viewAny(Account|User $account): bool
    {
        return $account->can('view_any_shield::role');
    }

    /**
     * Determine whether the account can view the model.
     */
    public function view(Account|User $account, Role $role): bool
    {
        return $account->can('view_shield::role');
    }

    /**
     * Determine whether the account can create models.
     */
    public function create(Account|User $account): bool
    {
        return $account->can('create_shield::role');
    }

    /**
     * Determine whether the account can update the model.
     */
    public function update(Account|User $account, Role $role): bool
    {
        if($role->name === 'super_admin' || $role->name === 'super-admin') {
            return false;
        }
        return $account->can('update_shield::role');
    }

    /**
     * Determine whether the account can delete the model.
     */
    public function delete(Account|User $account, Role $role): bool
    {
        if($role->name === 'super_admin' || $role->name === 'super-admin') {
            return false;
        }
        return $account->can('delete_shield::role');
    }

    /**
     * Determine whether the account can bulk delete.
     */
    public function deleteAny(Account|User $account): bool
    {
        return false;
        
       // return $account->can('delete_any_shield::role');
    }

    /**
     * Determine whether the account can permanently delete.
     */
    public function forceDelete(Account|User $account, Role $role): bool
    {
        if($role->name === 'super_admin' || $role->name === 'super-admin') {
            return false;
        }
        return $account->can('force_delete_shield::role');
    }

    /**
     * Determine whether the account can permanently bulk delete.
     */
    public function forceDeleteAny(Account|User $account): bool
    {
        return $account->can('force_delete_any_shield::role');
    }

    /**
     * Determine whether the account can restore.
     */
    public function restore(Account|User $account, Role $role): bool
    {
        return $account->can('restore_shield::role');
    }

    /**
     * Determine whether the account can bulk restore.
     */
    public function restoreAny(Account|User $account): bool
    {
        return $account->can('restore_any_shield::role');
    }

    /**
     * Determine whether the account can replicate.
     */
    public function replicate(Account|User $account, Role $role): bool
    {
        return $account->can('replicate_shield::role');
    }

    /**
     * Determine whether the account can reorder.
     */
    public function reorder(Account|User $account): bool
    {
        return $account->can('reorder_shield::role');
    }
}
