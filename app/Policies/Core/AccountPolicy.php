<?php

namespace App\Policies\Core;

use App\Models\User;

use App\Models\Core\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the account can view any models.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function viewAny(Account|User $account): bool
    {
        return $account->can('view_any_core::account');
    }

    /**
     * Determine whether the account can view the model.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function view(Account|User $account): bool
    {
        return $account->can('view_core::account');
    }

    /**
     * Determine whether the account can create models.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function create(Account|User $account): bool
    {
        return $account->can('create_core::account');
    }

    /**
     * Determine whether the account can update the model.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function update(Account|User $account): bool
    {
        return $account->can('update_core::account');
    }

    /**
     * Determine whether the account can delete the model.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function delete(Account|User $account): bool
    {
        return $account->can('delete_core::account');
    }

    /**
     * Determine whether the account can bulk delete.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function deleteAny(Account|User $account): bool
    {
        return $account->can('delete_any_core::account');
    }

    /**
     * Determine whether the account can permanently delete.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function forceDelete(Account|User $account): bool
    {
        return $account->can('force_delete_core::account');
    }

    /**
     * Determine whether the account can permanently bulk delete.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function forceDeleteAny(Account|User $account): bool
    {
        return $account->can('force_delete_any_core::account');
    }

    /**
     * Determine whether the account can restore.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function restore(Account|User $account): bool
    {
        return $account->can('restore_core::account');
    }

    /**
     * Determine whether the account can bulk restore.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function restoreAny(Account|User $account): bool
    {
        return $account->can('restore_any_core::account');
    }

    /**
     * Determine whether the account can bulk restore.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function replicate(Account|User $account): bool
    {
        return $account->can('replicate_core::account');
    }

    /**
     * Determine whether the account can reorder.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function reorder(Account|User $account): bool
    {
        return $account->can('reorder_core::account');
    }
}
