<?php

namespace App\Policies\Core;

use App\Models\User;
use App\Models\Core\Account;
use App\Models\Core\Category;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the account can view any models.
     */
    public function viewAny(Account|User $account): bool
    {
        return $account->can('view_any_core::category');
    }

    /**
     * Determine whether the account can view the model.
     */
    public function view(Account|User $account, Category $category): bool
    {
        return $account->can('view_core::category');
    }

    /**
     * Determine whether the account can create models.
     */
    public function create(Account|User $account): bool
    {
        return $account->can('create_core::category');
    }

    /**
     * Determine whether the account can update the model.
     */
    public function update(Account|User $account, Category $category): bool
    {
        return $account->can('update_core::category');
    }

    /**
     * Determine whether the account can delete the model.
     */
    public function delete(Account|User $account, Category $category): bool
    {
        return $account->can('delete_core::category');
    }

    /**
     * Determine whether the account can bulk delete.
     */
    public function deleteAny(Account|User $account): bool
    {
        return $account->can('delete_any_core::category');
    }

    /**
     * Determine whether the account can permanently delete.
     */
    public function forceDelete(Account|User $account, Category $category): bool
    {
        return $account->can('force_delete_core::category');
    }

    /**
     * Determine whether the account can permanently bulk delete.
     */
    public function forceDeleteAny(Account|User $account): bool
    {
        return $account->can('force_delete_any_core::category');
    }

    /**
     * Determine whether the account can restore.
     */
    public function restore(Account|User $account, Category $category): bool
    {
        return $account->can('restore_core::category');
    }

    /**
     * Determine whether the account can bulk restore.
     */
    public function restoreAny(Account|User $account): bool
    {
        return $account->can('restore_any_core::category');
    }

    /**
     * Determine whether the account can replicate.
     */
    public function replicate(Account|User $account, Category $category): bool
    {
        return $account->can('replicate_core::category');
    }

    /**
     * Determine whether the account can reorder.
     */
    public function reorder(Account|User $account): bool
    {
        return $account->can('reorder_core::category');
    }
}
