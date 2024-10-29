<?php

namespace App\Policies\Core;

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
    public function viewAny(Account $account): bool
    {
        return $account->can('view_any_core::account');
    }

    /**
     * Determine whether the account can view the model.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function view(Account $account): bool
    {
        return $account->can('view_core::account');
    }

    /**
     * Determine whether the account can create models.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function create(Account $account): bool
    {
        return $account->can('create_core::account');
    }

    /**
     * Determine whether the account can update the model.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function update(Account $account): bool
    {
        return $account->can('update_core::account');
    }

    /**
     * Determine whether the account can delete the model.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function delete(Account $account): bool
    {
        return $account->can('delete_core::account');
    }

    /**
     * Determine whether the account can bulk delete.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function deleteAny(Account $account): bool
    {
        return $account->can('delete_any_core::account');
    }

    /**
     * Determine whether the account can permanently delete.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function forceDelete(Account $account): bool
    {
        return $account->can('force_delete_core::account');
    }

    /**
     * Determine whether the account can permanently bulk delete.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function forceDeleteAny(Account $account): bool
    {
        return $account->can('force_delete_any_core::account');
    }

    /**
     * Determine whether the account can restore.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function restore(Account $account): bool
    {
        return $account->can('restore_core::account');
    }

    /**
     * Determine whether the account can bulk restore.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function restoreAny(Account $account): bool
    {
        return $account->can('restore_any_core::account');
    }

    /**
     * Determine whether the account can bulk restore.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function replicate(Account $account): bool
    {
        return $account->can('replicate_core::account');
    }

    /**
     * Determine whether the account can reorder.
     *
     * @param  \App\Models\Core\Account  $account
     * @return bool
     */
    public function reorder(Account $account): bool
    {
        return $account->can('reorder_core::account');
    }
}
