<?php

namespace App\Core\States\Order;

use App\Core\States\Order\OrderState;
use App\Models\Core\Order;

class ShippedState extends OrderState
{
    private string $trackingNumber;



    public function __construct(Order $order, string $trackingNumber){
        $this->trackingNumber = $trackingNumber;
        parent::__construct($order);
    }

    public function getTrackingNumber(): ?string{
        return $this->trackingNumber;
    }
}
