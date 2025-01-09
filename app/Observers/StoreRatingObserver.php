<?php

namespace App\Observers;

use App\Models\StoreRating;

class StoreRatingObserver
{
    /**
     * Handle the StoreRating "created" event.
     */
    public function created(StoreRating $storeRating): void
    {
        $this->updateStoreRating($storeRating);
    }

    /**
     * Handle the StoreRating "updated" event.
     */
    public function updated(StoreRating $storeRating): void
    {
        $this->updateStoreRating($storeRating);
    }

    /**
     * Handle the StoreRating "deleted" event.
     */
    public function deleted(StoreRating $storeRating): void
    {
        $this->updateStoreRating($storeRating);
    }

    /**
     * Update the average rating of the store.
     */
    protected function updateStoreRating(StoreRating $storeRating): void
    {
        $store = $storeRating->store;

        if ($store) {
            $averageRating = $store->ratings()->avg('rating');

            if ($averageRating !== null) {
                $store->average_rating = $averageRating;
                $store->save();
            } else {
                $store->average_rating = null;
                $store->save();
            }
        }
    }

    /**
     * Handle the StoreRating "restored" event.
     */
    public function restored(StoreRating $storeRating): void
    {
        $this->updateStoreRating($storeRating);
    }

    /**
     * Handle the StoreRating "force deleted" event.
     */
    public function forceDeleted(StoreRating $storeRating): void
    {
        $this->updateStoreRating($storeRating);
    }
}
