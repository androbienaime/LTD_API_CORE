<?php

// ════════════════════════════════════════════════════════════════════════════
//  CheckShopPermission.php
//  Usage: ->middleware('shop.permission:product.create')
//
//  - Requête JWT  → lit les permissions depuis le payload (0 DB query)
//  - Requête session → fallback sur ShopPermissionService (DB)
// ════════════════════════════════════════════════════════════════════════════

namespace App\Http\Middleware;

use App\Models\Core\Shop;
use App\Services\ShopPermissionService;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckShopPermission
{
    public function __construct(
        private readonly ShopPermissionService $permissions
    ) {}

    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        $account = auth('account')->user()
            ?? auth('account-service')->user();

        if (! $account) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $shop = $this->resolveShop($request);

        if (! $shop) {
            return response()->json(['message' => 'Shop introuvable.'], 404);
        }

        if ($request->bearerToken()) {
            $payload   = FacadesJWTAuth::parseToken()->getPayload();
            $perms     = $payload->get('permissions') ?? [];
            $shopPerms = $perms[(string) $shop->id] ?? [];

            // Convertit "customer.view" → ["view_core::customer", "view_any_core::customer"]
            $spatiePerms = $this->toSpatiePermissions($permission);

            $hasPermission = collect($spatiePerms)
                ->some(fn ($p) => in_array($p, $shopPerms));

            if (! $hasPermission) {
                return response()->json(['message' => 'Permission refusée.'], 403);
            }
        } else {
            if (! $this->permissions->hasPermission($account, $shop, $permission)) {
                return response()->json(['message' => 'Permission refusée.'], 403);
            }
        }

        $request->attributes->set('resolved_shop', $shop);

        return $next($request);
    }

    /**
     * Convertit le format custom "model.action" vers le format Spatie Shield.
     * "customer.view"   → ["view_core::customer",  "view_any_core::customer"]
     * "customer.create" → ["create_core::customer"]
     * "product.delete"  → ["delete_core::product",  "delete_any_core::product"]
     */
    private function toSpatiePermissions(string $permission): array
    {
        [$model, $action] = explode('.', $permission, 2);

        $base = "{$action}_core::{$model}";

        // "view" et "delete" ont une variante "any" dans Spatie Shield
        $withAny = in_array($action, ['view', 'delete', 'restore', 'force_delete'])
            ? ["{$action}_any_core::{$model}"]
            : [];

        return array_merge([$base], $withAny);
    }

    private function resolveShop(Request $request): ?Shop
    {
        $shop = $request->route('shop');

        if ($shop instanceof Shop) return $shop;
        if ($shop) return Shop::find($shop);

        return Shop::find($request->header('X-Shop-Id'));
    }
}