<?php

namespace App\Observers;

use App\Models\StoreRating;
use Illuminate\Support\Facades\Log;


class StoreRatingObserver
{
    /**
     * Handle the StoreRating "created" event.
     */
    public function created(StoreRating $storeRating): void
    {Log::info("StoreRatingObserver called for event: created");

        $this->updateStoreRating($storeRating);
    }

    /**
     * Handle the StoreRating "updated" event.
     */
    public function updated(StoreRating $storeRating): void
    {Log::info("StoreRatingObserver called for event: created");

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
    protected function updateStoreRating(StoreRating $storeRating)//: void
    {
 Log::info("Updating store rating for store ID: {$storeRating->store_id}");
        $store = $storeRating->store;

        if ($store) {
            $averageRating = $store->ratings()->avg('rating');

            if ($averageRating !== null) {
                $store->store_rate = $averageRating;
                $store->save();
            } else {
                $store->store_rate  = null;
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
