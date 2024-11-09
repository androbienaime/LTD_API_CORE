<?php

namespace App\Core\States\Order\Exception;

class OrderTransitionException extends \Exception
{

    /**
     * @param string $string
     */
    public function __construct(string $string)
    {
    }
}
