<?php

namespace App\Policies\Core;

use App\Models\User;
use App\Models\Core\Account;
use App\Models\Core\Tracking;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrackingPolicy
{
    use HandlesAuthorization;

     /**
     * This method runs before each permission check.
     * It blocks all access for users under the 'account' guard,
     * unless an exception is defined in a specific policy method.
     */

     public function before(Account|User $account, $ability): bool|null
     {
         // Si le guard actif est 'account', bloquer l'accès globalement
         if (auth()->guard('account')->check()) {
             return false;
         }
 
         // Retourne null pour continuer la vérification des permissions normales
         return null;
     }

    /**
     * Determine whether the account can view any models.
     */
    public function viewAny(Account|User $account): bool
    {
        return $account->can('view_any_core::tracking');
    }

    /**
     * Determine whether the account can view the model.
     */
    public function view(Account|User $account, Tracking $tracking): bool
    {
        return $account->can('view_core::tracking');
    }

    /**
     * Determine whether the account can create models.
     */
    public function create(Account|User $account): bool
    {
        return $account->can('create_core::tracking');
    }

    /**
     * Determine whether the account can update the model.
     */
    public function update(Account|User $account, Tracking $tracking): bool
    {
        return $account->can('update_core::tracking');
    }

    /**
     * Determine whether the account can delete the model.
     */
    public function delete(Account|User $account, Tracking $tracking): bool
    {
        return $account->can('delete_core::tracking');
    }

    /**
     * Determine whether the account can bulk delete.
     */
    public function deleteAny(Account|User $account): bool
    {
        return $account->can('delete_any_core::tracking');
    }

    /**
     * Determine whether the account can permanently delete.
     */
    public function forceDelete(Account|User $account, Tracking $tracking): bool
    {
        return $account->can('force_delete_core::tracking');
    }

    /**
     * Determine whether the account can permanently bulk delete.
     */
    public function forceDeleteAny(Account|User $account): bool
    {
        return $account->can('force_delete_any_core::tracking');
    }

    /**
     * Determine whether the account can restore.
     */
    public function restore(Account|User $account, Tracking $tracking): bool
    {
        return $account->can('restore_core::tracking');
    }

    /**
     * Determine whether the account can bulk restore.
     */
    public function restoreAny(Account|User $account): bool
    {
        return $account->can('restore_any_core::tracking');
    }

    /**
     * Determine whether the account can replicate.
     */
    public function replicate(Account|User $account, Tracking $tracking): bool
    {
        return $account->can('replicate_core::tracking');
    }

    /**
     * Determine whether the account can reorder.
     */
    public function reorder(Account|User $account): bool
    {
        return $account->can('reorder_core::tracking');
    }
}
