<?php

namespace App\Observers;

use App\Core\Trait\Models\AccountShopTrait;
use App\Models\Core\Account;
use App\Models\Core\Customer;

class CustomerObserver extends BaseObserver
{
    /**
     * Handle the Customer "created" event.
     * fill tenant merchant_id
     */
    public function creating(Customer $customer): void
    {
        $this->setCommonFields($customer);
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }
}
