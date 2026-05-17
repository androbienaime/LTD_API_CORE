<?php
// app/Services/ShopPermissionService.php

namespace App\Services;

use App\Models\Core\{Account, AccountShop, Shop};
use Spatie\Permission\Models\Role;

class ShopPermissionService
{
    public function hasPermission(Account $account, Shop $shop, string $permission): bool
    {
        $role = $this->getRoleInShop($account, $shop);

        return $role?->hasPermissionTo($permission, 'account') ?? false;
    }

    public function hasAnyPermission(Account $account, Shop $shop, array $permissions): bool
    {
        $role = $this->getRoleInShop($account, $shop);

        return $role?->hasAnyPermission($permissions) ?? false;
    }

    public function hasAllPermissions(Account $account, Shop $shop, array $permissions): bool
    {
        $role = $this->getRoleInShop($account, $shop);

        return $role?->hasAllPermissions($permissions) ?? false;
    }

    public function hasRole(Account $account, Shop $shop, string|array $roles): bool
    {
        $role = $this->getRoleInShop($account, $shop);

        return $role && in_array($role->name, (array) $roles);
    }

    public function getRoleName(Account $account, Shop $shop): ?string
    {
        return $this->getRoleInShop($account, $shop)?->name;
    }

    private function getRoleInShop(Account $account, Shop $shop): ?Role
    {
        $roleId = AccountShop::where('account_id', $account->id)
            ->where('shop_id', $shop->id)
            ->value('role_id');

        return $roleId ? Role::find($roleId) : null;
    }
}