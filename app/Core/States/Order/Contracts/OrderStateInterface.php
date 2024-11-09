<?php

namespace App\Core\States\Order\Contracts;

interface OrderStateInterface
{
    public function canShipOrder() : bool;
}
