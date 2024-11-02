<?php

namespace App\Core\Trait\Observer;
use App\Models\Core\Account;

trait HasCommonFieldsTrait
{
    use \App\Core\Trait\Models\AccountShopTrait;

    public function setCommonFields($model){
        if (auth('account')->check()) {
            $account = Account::all()->find(auth("account")->id());
            if(!is_null($this->findShopByAccount($account))){
                $model->merchant_id = $account->id;
                $model->shop_id = $this->findShopByAccount($account)->id;
            }
        }
    }
}
