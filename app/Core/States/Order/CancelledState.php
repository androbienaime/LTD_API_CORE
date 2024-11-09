<?php

namespace App\Core\States\Order;

use App\Core\States\Order\OrderState;
use App\Models\Core\Order;

class CancelledState extends OrderState
{
    private ?string $reason = null;

    public function __construct(Order $order, string $reason)
    {
        parent::__construct($order);
        $this->reason = $reason;
    }
    public function getReason(): ?string{
        return $this->reason;
    }
}
