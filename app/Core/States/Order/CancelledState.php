<?php

namespace App\Core\States\Order;

use App\Core\States\Order\OrderState;
use App\Models\Core\Order;
use Spatie\ModelStates\Transition;

class CancelledState extends OrderState
{
    private ?string $reason;

    public function label() : string
    {
        return "Cancelled";
    }

    public function color() : string
    {
        return "danger";
    }

    public function __construct(Order $order)
    {
        parent::__construct($order);
    }
    public function getReason(): ?string{
        return $this->reason;
    }

    public function setReason(string $reason){
        $this->reason = $reason;
    }
}
