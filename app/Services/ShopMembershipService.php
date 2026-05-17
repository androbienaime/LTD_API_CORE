<?php
// app/Services/ShopMembershipService.php

namespace App\Services;

use App\Models\Core\{Account, AccountShop, Shop};
use App\Exceptions\ShopMembershipException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ShopMembershipService
{
    const ROLE_SUPER_ADMIN = 'super-admin';

    // ──────────────────────────────────────────────
    // AJOUTER un account à un shop
    // ──────────────────────────────────────────────
    public function attach(Shop $shop, Account $account, string $roleName): AccountShop
    {
        $role = $this->resolveRole($roleName);

        // Déjà membre actif ?
        $existing = AccountShop::where('shop_id', $shop->id)
            ->where('account_id', $account->id)
            ->first();

        if ($existing && is_null($existing->deleted_at)) {
            throw new ShopMembershipException("Ce compte est déjà membre du shop.");
        }

        // Restore si soft-deleted, sinon create
        if ($existing?->trashed()) {
            $existing->restore();
            $existing->update(['role_id' => $role->id]);
            return $existing->fresh();
        }

        return AccountShop::create([
            'shop_id'    => $shop->id,
            'account_id' => $account->id,
            'role_id'    => $role->id,
        ]);
    }

    // ──────────────────────────────────────────────
    // METTRE À JOUR le rôle
    // ──────────────────────────────────────────────
    public function updateRole(Shop $shop, Account $account, string $newRoleName): AccountShop
    {
        $membership = $this->findMembershipOrFail($shop, $account);
        $newRole    = $this->resolveRole($newRoleName);

        // Contrainte : dernier super_admin ?
        if ($membership->isSuperAdmin() && $newRoleName !== self::ROLE_SUPER_ADMIN) {
            $this->ensureNotLastSuperAdmin($shop, $membership);
        }

        $membership->update(['role_id' => $newRole->id]);

        return $membership->fresh();
    }

    // ──────────────────────────────────────────────
    // DÉTACHER (soft delete)
    // ──────────────────────────────────────────────
    public function detach(Shop $shop, Account $account): void
    {
        $membership = $this->findMembershipOrFail($shop, $account);

        if ($membership->isSuperAdmin()) {
            $this->ensureNotLastSuperAdmin($shop, $membership);
        }

        $membership->delete(); // SoftDelete → observer "deleting" déclenché
    }

    // ──────────────────────────────────────────────
    // SYNC depuis Filament (tableau de membres)
    //
    // $members = [
    //   ['account_id' => 1, 'role_id' => 3],
    //   ['account_id' => 2, 'role_id' => 5],
    // ]
    // ──────────────────────────────────────────────
    public function sync(Shop $shop, array $members): void
    {
        DB::transaction(function () use ($shop, $members) {
            // Valider d'abord (avant toute modif)
            $this->validateSyncPayload($shop, $members);

            $incomingAccountIds = collect($members)->pluck('account_id')->all();

            // Détacher ceux qui ne sont plus dans la liste
            AccountShop::where('shop_id', $shop->id)
                ->whereNotIn('account_id', $incomingAccountIds)
                ->get()
                ->each(fn ($m) => $this->detach($shop, Account::find($m->account_id)));

            // Attacher ou mettre à jour chacun
            foreach ($members as $member) {
                $account = Account::findOrFail($member['account_id']);
                $role    = Role::findOrFail($member['role_id']);

                $existing = AccountShop::withTrashed()
                    ->where('shop_id', $shop->id)
                    ->where('account_id', $account->id)
                    ->first();

                if ($existing?->trashed()) {
                    $existing->restore();
                    $existing->update(['role_id' => $role->id]);
                } elseif ($existing) {
                    if ($existing->role_id !== $role->id) {
                        $this->updateRole($shop, $account, $role->name);
                    }
                } else {
                    $this->attach($shop, $account, $role->name);
                }
            }
        });
    }

    // ──────────────────────────────────────────────
    // CRÉER le shop + ajouter le créateur en super_admin
    // ──────────────────────────────────────────────
    public function createShopWithOwner(array $shopData, Account $owner): Shop
    {
        return DB::transaction(function () use ($shopData, $owner) {
            $shop = Shop::create($shopData);
            $this->attach($shop, $owner, self::ROLE_SUPER_ADMIN);
            return $shop;
        });
    }

    // ──────────────────────────────────────────────
    // HELPERS PRIVÉS
    // ──────────────────────────────────────────────
    private function resolveRole(string $roleName): Role
    {
        $role = Role::findByName($roleName, 'account');

        if (! $role) {
            throw new ShopMembershipException("Rôle '{$roleName}' introuvable (guard: account).");
        }

        return $role;
    }

    private function findMembershipOrFail(Shop $shop, Account $account): AccountShop
    {
        $membership = AccountShop::where('shop_id', $shop->id)
            ->where('account_id', $account->id)
            ->first();

        if (! $membership) {
            throw new ShopMembershipException("Ce compte n'est pas membre de ce shop.");
        }

        return $membership;
    }

    private function countSuperAdmins(Shop $shop, ?int $excludeId = null): int
    {
        $superAdminRole = $this->resolveRole(self::ROLE_SUPER_ADMIN);

        return AccountShop::where('shop_id', $shop->id)
            ->where('role_id', $superAdminRole->id)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->count();
    }

    private function ensureNotLastSuperAdmin(Shop $shop, AccountShop $membership): void
    {
        if ($this->countSuperAdmins($shop, $membership->id) === 0) {
            throw new ShopMembershipException(
                "Impossible : ce compte est le dernier super_admin du shop."
            );
        }
    }

    private function validateSyncPayload(Shop $shop, array $members): void
    {
        $superAdminRole = $this->resolveRole(self::ROLE_SUPER_ADMIN);

        $superAdminCount = collect($members)
            ->where('role_id', $superAdminRole->id)
            ->count();

        if ($superAdminCount === 0) {
            throw new ShopMembershipException(
                "Le shop doit avoir au moins un super_admin."
            );
        }
    }
}