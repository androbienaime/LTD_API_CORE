<?php

namespace App\Observers;

use App\Models\Core\Brand;

class BrandObserver extends BaseObserver
{
    /**
     * Handle the Brand "created" event.
     */
    public function creating(Brand $brand): void
    {
        $this->setCommonFields($brand);
    }

    /**
     * Handle the Brand "updated" event.
     */
    public function updated(Brand $brand): void
    {
        //
    }

    /**
     * Handle the Brand "deleted" event.
     */
    public function deleted(Brand $brand): void
    {
        //
    }

    /**
     * Handle the Brand "restored" event.
     */
    public function restored(Brand $brand): void
    {
        //
    }

    /**
     * Handle the Brand "force deleted" event.
     */
    public function forceDeleted(Brand $brand): void
    {
        //
    }
}
