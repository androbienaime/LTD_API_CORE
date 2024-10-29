<?php

namespace App\Policies\Core;

use App\Models\Core\Account;
use App\Models\Core\Coupon;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the account can view any models.
     */
    public function viewAny(Account $account): bool
    {
        return $account->can('view_any_core::coupon');
    }

    /**
     * Determine whether the account can view the model.
     */
    public function view(Account $account, Coupon $coupon): bool
    {
        return $account->can('view_core::coupon');
    }

    /**
     * Determine whether the account can create models.
     */
    public function create(Account $account): bool
    {
        return $account->can('create_core::coupon');
    }

    /**
     * Determine whether the account can update the model.
     */
    public function update(Account $account, Coupon $coupon): bool
    {
        return $account->can('update_core::coupon');
    }

    /**
     * Determine whether the account can delete the model.
     */
    public function delete(Account $account, Coupon $coupon): bool
    {
        return $account->can('delete_core::coupon');
    }

    /**
     * Determine whether the account can bulk delete.
     */
    public function deleteAny(Account $account): bool
    {
        return $account->can('delete_any_core::coupon');
    }

    /**
     * Determine whether the account can permanently delete.
     */
    public function forceDelete(Account $account, Coupon $coupon): bool
    {
        return $account->can('force_delete_core::coupon');
    }

    /**
     * Determine whether the account can permanently bulk delete.
     */
    public function forceDeleteAny(Account $account): bool
    {
        return $account->can('force_delete_any_core::coupon');
    }

    /**
     * Determine whether the account can restore.
     */
    public function restore(Account $account, Coupon $coupon): bool
    {
        return $account->can('restore_core::coupon');
    }

    /**
     * Determine whether the account can bulk restore.
     */
    public function restoreAny(Account $account): bool
    {
        return $account->can('restore_any_core::coupon');
    }

    /**
     * Determine whether the account can replicate.
     */
    public function replicate(Account $account, Coupon $coupon): bool
    {
        return $account->can('replicate_core::coupon');
    }

    /**
     * Determine whether the account can reorder.
     */
    public function reorder(Account $account): bool
    {
        return $account->can('reorder_core::coupon');
    }
}
