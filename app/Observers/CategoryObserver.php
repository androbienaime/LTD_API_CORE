<?php

namespace App\Observers;

use App\Models\Core\Category;

class CategoryObserver extends BaseObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function creating(Category $category): void
    {
        $this->setCommonFields($category);
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        //
    }
}
