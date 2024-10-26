<?php

namespace App\Core\Trait\Models;
use Illuminate\Contracts\Database\Eloquent\Builder;

trait AccountGlobalScopeTrait{
    protected static function booted(): void
    {
        // Dans le modèle Customer
        static::addGlobalScope('account', function (Builder $query) {
            if (auth("account")->check()) {
                $account = auth("account")->user();

                $query->whereExists(function ($subQuery) use ($account) {
                    $subQuery->from('account_shop')
                        ->whereColumn('account_shop.shop_id', self::$tableName.'.shop_id')
                        ->where('account_shop.account_id', $account->id)
                        ->where('account_shop.status', "active");
                });
            }
        });


    }
}
