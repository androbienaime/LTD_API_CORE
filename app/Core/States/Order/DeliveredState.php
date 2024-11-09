<?php

namespace App\Core\States\Order;

use App\Core\States\Order\OrderState;

class DeliveredState extends OrderState
{
    private \DateTime $deliveredAt;

    public function __construct(\DateTime $deliveredAt){
        $this->deliveredAt = $deliveredAt;
    }


}
