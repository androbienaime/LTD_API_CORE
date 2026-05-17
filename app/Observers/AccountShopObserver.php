<?php

namespace App\Observers;

use App\Exceptions\ShopMembershipException;
use App\Models\Core\AccountShop;
use App\Models\Core\Roles\Role;
use Illuminate\Validation\ValidationException;

class AccountShopObserver
{
    /**
     * Handle the AccountShop "created" event.
     */
    public function created(AccountShop $accountShop): void
    {
        //
    }

    /**
     * Handle the AccountShop "updated" event.
     */
    public function updating(AccountShop $accountShop): void
    {
         $superAdminRoleId = Role::where('name', 'super-admin')
            ->where('guard_name', 'account')
            ->value('id');

        // 🔥 Si changement de rôle
        if ($accountShop->isDirty('role_id')) {

            $oldRoleId = $accountShop->getOriginal('role_id');

            // 🔴 si on retire super-admin
            if (
                $oldRoleId == $superAdminRoleId &&
                $accountShop->role_id != $superAdminRoleId
            ) {
                $count = $accountShop->shop->accounts()
                    ->wherePivot('role_id', $superAdminRoleId)
                    ->count();

                if ($count <= 1) {
                    throw ValidationException::withMessages([
                        'role_id' => 'Le shop doit avoir au moins un super admin.',
                    ]);
                }
            }
        }
    }

    /**
     * Handle the AccountShop "deleted" event.
     */
    public function deleted(AccountShop $accountShop): void
    {
                $superAdminRoleId = Role::where('name', 'super-admin')
            ->where('guard_name', 'account')
            ->value('id');

        // 🔴 si suppression d’un super admin
        if ($accountShop->role_id == $superAdminRoleId) {

            $count = $accountShop->shop->accounts()
                ->wherePivot('role_id', $superAdminRoleId)
                ->count();

            if ($count <= 1) {
                throw ValidationException::withMessages([
                    'account' => 'Impossible de supprimer le dernier super admin.',
                ]);
            }
        }
    }

    /**
     * Handle the AccountShop "restored" event.
     */
    public function restored(AccountShop $accountShop): void
    {
        //
    }

    /**
     * Handle the AccountShop "force deleted" event.
     */
    public function forceDeleted(AccountShop $accountShop): void
    {
        //
    }

    public function deleting(AccountShop $membership): void
    {
        // Soft delete : on vérifie uniquement si c'est un super_admin
        if (! $membership->isSuperAdmin()) {
            return;
        }

        $superAdminRole = Role::findByName('super-admin', 'account');

        $remainingSuperAdmins = AccountShop::where('shop_id', $membership->shop_id)
            ->where('role_id', $superAdminRole->id)
            ->where('id', '!=', $membership->id)
            ->count();

        if ($remainingSuperAdmins === 0) {
            throw new ShopMembershipException(
                "Suppression impossible : dernier super_admin du shop #{$membership->shop_id}."
            );
        }
    }
}
