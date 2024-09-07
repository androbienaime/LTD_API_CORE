<?php

namespace App\Core\Trait;

use App\Models\Core\Delivery;

trait ManagesShippingTrait {

    public static function processShipping(array $data){ 
        $delivery = Delivery::createOrFirst($data);
        unset($data);
        $data["delivery_id"] = $delivery->id;
  

        
        return $data;
    }
}
