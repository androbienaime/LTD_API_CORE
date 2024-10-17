<?php

namespace App\Core\Trait;

use App\Models\Core\Customer;

trait CustomerTrait
{

    public static function hasCustomerAddress(?Customer $customer){
        $hasAddress = false;
        if($customer){
            if($customer->addressCustomers()->count() > 0){
                $hasAddress = true;
            }
        }

        return $hasAddress;
    }

}
