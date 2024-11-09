<?php

namespace App\Core\States\Order;

use App\Models\Core\Order;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class OrderState extends State
{
    protected $model = Order::class;

    public function __construct($model)
    {
        parent::__construct($model);
    }

    public static function config() : StateConfig
    {
        return parent::config()
            ->default(PendingState::class)
            ->allowTransition(PendingState::class, ProcessingState::class)
            ->allowTransition(ProcessingState::class, ShippedState::class)
            ->allowTransition(ShippedState::class, DeliveredState::class)
            ->allowTransition([PendingState::class, ProcessingState::class], CancelledState::class)
            ->allowTransition(DeliveredState::class, ReturnedState::class)
            ->registerState([
                PendingState::class,
                ProcessingState::class,
                ShippedState::class,
                DeliveredState::class,
                CancelledState::class,
                ReturnedState::class,
            ]);
    }
}
