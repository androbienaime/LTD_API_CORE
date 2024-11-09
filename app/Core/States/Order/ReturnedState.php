<?php

namespace App\Core\States\Order;

use App\Core\States\Order\OrderState;
use App\Models\Core\Order;

class ReturnedState extends OrderState
{
    private string $returnReason;
    private \DateTime $returnedAt;

    public function __construct(Order $order, string $returnReason, \DateTime $returnedAt = null){
        parent::__construct($order);

        $this->returnReason = $returnReason;
        $this->returnedAt = $returnedAt;
    }

}
