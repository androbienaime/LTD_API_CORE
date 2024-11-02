<?php

namespace App\Core\Trait\Models;

use App\Models\Core\Account;
use App\Models\Core\AccountShop;
use App\Models\Core\Shop;

trait AccountShopTrait{

    public function findShopByAccount(Account $account){
        $shop = null;

        if($account){
            $shopPivot =  AccountShop::where("account_id", $account->id)
                ->where("status", "active")
                ->first();

            (!is_null($shopPivot)) ? $shop = Shop::find($shopPivot)->first() : null;
        }

        return $shop;
    }
}
