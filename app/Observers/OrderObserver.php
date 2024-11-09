<?php

namespace App\Observers;

use App\Core\States\Order\Exception\OrderTransitionException;
use App\Core\States\Order\PendingState;
use App\Models\Core\Order;

class OrderObserver extends BaseObserver
{
    public function processOrder(Order $order)
    {
        try {
            $order->process();
            return response()->json(['message' => 'Order processed successfully']);
        } catch (OrderTransitionException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function creating(Order $order): void
    {
        $this->setCommonFields($order);

        if($order->order_amount > 0){
            $this->processOrder($order);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if($order->state instanceof PendingState){
            if($order->order_amount > 0){
                $order->process($order)->save();
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
