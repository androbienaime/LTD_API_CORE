<?php

namespace App\Policies;

use App\Models\Core\Account;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the account can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return $account->can('view_any_core::user');
    }

    /**
     * Determine whether the account can view the model.
     */
    public function view(Account $account, User $user): bool
    {
        return $account->can('view_core::user');
    }

    /**
     * Determine whether the account can create models.
     */
    public function create(Account $account): bool
    {
        return $account->can('create_core::user');
    }

    /**
     * Determine whether the account can update the model.
     */
    public function update(Account $account, User $user): bool
    {
        return $account->can('update_core::user');
    }

    /**
     * Determine whether the account can delete the model.
     */
    public function delete(Account $account, User $user): bool
    {
        return $account->can('delete_core::user');
    }

    /**
     * Determine whether the account can bulk delete.
     */
    public function deleteAny(Account $account): bool
    {
        return $account->can('delete_any_core::user');
    }

    /**
     * Determine whether the account can permanently delete.
     */
    public function forceDelete(Account $account, User $user): bool
    {
        return $account->can('force_delete_core::user');
    }

    /**
     * Determine whether the account can permanently bulk delete.
     */
    public function forceDeleteAny(Account $account): bool
    {
        return $account->can('force_delete_any_core::user');
    }

    /**
     * Determine whether the account can restore.
     */
    public function restore(Account $account, User $user): bool
    {
        return $account->can('restore_core::user');
    }

    /**
     * Determine whether the account can bulk restore.
     */
    public function restoreAny(Account $account): bool
    {
        return $account->can('restore_any_core::user');
    }

    /**
     * Determine whether the account can replicate.
     */
    public function replicate(Account $account, User $user): bool
    {
        return $account->can('replicate_core::user');
    }

    /**
     * Determine whether the account can reorder.
     */
    public function reorder(Account $account): bool
    {
        return $account->can('reorder_core::user');
    }
}
