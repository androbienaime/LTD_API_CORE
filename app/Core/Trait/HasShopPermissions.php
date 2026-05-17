<?php
// app/Traits/HasShopPermissions.php

namespace App\Core\Trait;

use App\Models\Core\Shop;
use App\Services\ShopPermissionService;

trait HasShopPermissions
{
    public function hasShopPermission(Shop $shop, string $permission): bool
    {
        return app(ShopPermissionService::class)
            ->hasPermission($this, $shop, $permission);
    }

    public function hasAnyShopPermission(Shop $shop, array $permissions): bool
    {
        return app(ShopPermissionService::class)
            ->hasAnyPermission($this, $shop, $permissions);
    }

    public function hasAllShopPermissions(Shop $shop, array $permissions): bool
    {
        return app(ShopPermissionService::class)
            ->hasAllPermissions($this, $shop, $permissions);
    }

    public function hasShopRole(Shop $shop, string|array $roles): bool
    {
        return app(ShopPermissionService::class)
            ->hasRole($this, $shop, $roles);
    }

    public function shopRole(Shop $shop): ?string
    {
        return app(ShopPermissionService::class)
            ->getRoleName($this, $shop);
    }
}