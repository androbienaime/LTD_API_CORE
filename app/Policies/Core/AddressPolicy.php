<?php

namespace App\Policies\Core;

use App\Models\Core\Account;
use App\Models\Core\Address;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the account can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return $account->can('view_any_core::address');
    }

    /**
     * Determine whether the account can view the model.
     */
    public function view(Account $account, Address $address): bool
    {
        return $account->can('view_core::address');
    }

    /**
     * Determine whether the account can create models.
     */
    public function create(Account $account): bool
    {
        return $account->can('create_core::address');
    }

    /**
     * Determine whether the account can update the model.
     */
    public function update(Account $account, Address $address): bool
    {
        return $account->can('update_core::address');
    }

    /**
     * Determine whether the account can delete the model.
     */
    public function delete(Account $account, Address $address): bool
    {
        return $account->can('delete_core::address');
    }

    /**
     * Determine whether the account can bulk delete.
     */
    public function deleteAny(Account $account): bool
    {
        return $account->can('delete_any_core::address');
    }

    /**
     * Determine whether the account can permanently delete.
     */
    public function forceDelete(Account $account, Address $address): bool
    {
        return $account->can('force_delete_core::address');
    }

    /**
     * Determine whether the account can permanently bulk delete.
     */
    public function forceDeleteAny(Account $account): bool
    {
        return $account->can('force_delete_any_core::address');
    }

    /**
     * Determine whether the account can restore.
     */
    public function restore(Account $account, Address $address): bool
    {
        return $account->can('restore_core::address');
    }

    /**
     * Determine whether the account can bulk restore.
     */
    public function restoreAny(Account $account): bool
    {
        return $account->can('restore_any_core::address');
    }

    /**
     * Determine whether the account can replicate.
     */
    public function replicate(Account $account, Address $address): bool
    {
        return $account->can('replicate_core::address');
    }

    /**
     * Determine whether the account can reorder.
     */
    public function reorder(Account $account): bool
    {
        return $account->can('reorder_core::address');
    }
}
